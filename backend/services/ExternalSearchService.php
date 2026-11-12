<?php

require_once __DIR__ . '/ExternalApiException.php';

class ExternalSearchService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config['external_api'] ?? [];
    }

    public function search(string $query, string $source, ?string $username, int $page): array
    {
        $query = trim($query);
        if ($query === '' || strlen($query) > 100) {
            throw new InvalidArgumentException('Search query must be between 1 and 100 characters.');
        }
        if ($page < 1 || $page > 100) {
            throw new InvalidArgumentException('Page must be between 1 and 100.');
        }

        if ($source === 'olx') {
            return $this->searchOlx($query, $username, $page);
        }
        if ($source !== 'dummyjson') {
            throw new InvalidArgumentException('Source must be dummyjson or olx.');
        }

        $baseUrl = rtrim((string) ($this->config['dummyjson_base_url'] ?? ''), '/');
        if ($baseUrl === '') {
            throw new RuntimeException('The public product search URL is not configured.');
        }
        $payload = $this->requestJson(
            $baseUrl . '/products/search?' . http_build_query(['q' => $query, 'limit' => 20, 'skip' => ($page - 1) * 20])
        );
        if (!isset($payload['products']) || !is_array($payload['products'])) {
            throw new RuntimeException('The public product search returned an unexpected response.');
        }

        $items = [];
        foreach ($payload['products'] as $product) {
            if (!is_array($product)) {
                continue;
            }
            $items[] = [
                'id' => $product['id'] ?? null,
                'title' => $product['title'] ?? '',
                'price' => $product['price'] ?? null,
                'currency' => 'USD',
                'thumbnail' => $product['thumbnail'] ?? null,
                'url' => isset($product['id']) ? 'https://dummyjson.com/products/' . rawurlencode((string) $product['id']) : null,
            ];
        }

        return [
            'source' => 'dummyjson',
            'query' => $query,
            'page' => $page,
            'total' => (int) ($payload['total'] ?? count($items)),
            'items' => $items,
        ];
    }

    private function searchOlx(string $query, ?string $username, int $page): array
    {
        if ($username === null || !preg_match('/^[A-Za-z0-9._-]{1,60}$/', $username)) {
            throw new InvalidArgumentException('A valid OLX seller username is required for OLX search.');
        }

        $token = trim((string) ($this->config['olx_api_token'] ?? ''));
        if ($token === '') {
            throw new ExternalApiException('OLX API access is not configured.', 401, 'olx_auth_required');
        }

        $baseUrl = rtrim((string) ($this->config['olx_base_url'] ?? 'https://api.olx.ba'), '/');
        $url = $baseUrl . '/users/' . rawurlencode($username) . '/listings?' . http_build_query(['page' => $page]);
        $payload = $this->requestJson($url, ['Authorization: Bearer ' . $token]);
        $listings = $this->extractListings($payload);
        $needle = strtolower($query);
        $matches = array_values(array_filter($listings, static function (array $listing) use ($needle): bool {
            $searchable = strtolower(
                (string) ($listing['title'] ?? '') . ' ' .
                (string) ($listing['short_description'] ?? '') . ' ' .
                (string) ($listing['slug'] ?? '')
            );
            return strpos($searchable, $needle) !== false;
        }));

        return [
            'source' => 'olx',
            'query' => $query,
            'username' => $username,
            'page' => $page,
            'total' => count($matches),
            'items' => $matches,
        ];
    }

    private function extractListings(array $payload): array
    {
        foreach (['data', 'listings', 'items'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                if (isset($payload[$key]['data']) && is_array($payload[$key]['data'])) {
                    return array_values(array_filter($payload[$key]['data'], 'is_array'));
                }
                return array_values(array_filter($payload[$key], 'is_array'));
            }
        }
        if (array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }
        throw new RuntimeException('OLX returned an unexpected listings response.');
    }

    private function requestJson(string $url, array $headers = []): array
    {
        $timeout = (int) ($this->config['timeout_seconds'] ?? 5);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => max(1, min($timeout, 15)),
                'ignore_errors' => true,
                'header' => implode("\r\n", array_merge(['Accept: application/json'], $headers)),
            ],
        ]);

        set_error_handler(static function (int $severity): void {
            throw new RuntimeException('External API request failed.');
        });
        try {
            $body = file_get_contents($url, false, $context);
        } catch (Throwable $exception) {
            throw new ExternalApiException('The external API could not be reached.', 500, 'upstream_unavailable');
        } finally {
            restore_error_handler();
        }

        if ($body === false) {
            throw new ExternalApiException('The external API could not be reached.', 500, 'upstream_unavailable');
        }

        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/\s([0-9]{3})\s/', $statusLine, $matches)) {
            $status = (int) $matches[1];
            if ($status === 401 || $status === 403 || $status === 404) {
                throw new ExternalApiException(
                    $status === 401 ? 'External API authentication failed.' : 'The external API denied or could not find the request.',
                    $status,
                    $status === 401 ? 'upstream_unauthorized' : ($status === 403 ? 'upstream_forbidden' : 'upstream_not_found')
                );
            }
            if ($status < 200 || $status >= 300) {
                throw new ExternalApiException('The external API returned an error.', 500, 'upstream_error');
            }
        }

        $payload = json_decode($body, true);
        if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            throw new ExternalApiException('The external API returned invalid JSON.', 500, 'upstream_invalid_response');
        }
        return $payload;
    }
}
