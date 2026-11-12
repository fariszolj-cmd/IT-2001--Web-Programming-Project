<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../middleware/ApiAuthMiddleware.php';
require_once __DIR__ . '/../services/ApiService.php';
require_once __DIR__ . '/../services/ExternalSearchService.php';

header('Content-Type: application/json; charset=utf-8');

$getApiService = static function (): ApiService {
    static $service = null;
    if ($service === null) {
        $service = new ApiService(Database::getConnection());
    }
    return $service;
};

$sendResult = static function (array $result): void {
    if ($result['status'] === 204) {
        Flight::response()->status(204);
        return;
    }
    Flight::json($result['body'], $result['status']);
};

$sendError = static function (Throwable $exception): void {
    if ($exception instanceof InvalidArgumentException) {
        Flight::json(['error' => $exception->getMessage()], 400);
        return;
    }
    if ($exception instanceof ExternalApiException) {
        Flight::json([
            'error' => $exception->getMessage(),
            'code' => $exception->getErrorCode(),
        ], $exception->getHttpStatus());
        return;
    }
    if ($exception instanceof PDOException && (string) $exception->getCode() === '23000') {
        Flight::json(['error' => 'The operation conflicts with an existing record or relationship.'], 400);
        return;
    }

    error_log('FarisCommerce API error: ' . get_class($exception));
    Flight::json(['error' => 'The server could not complete the request.'], 500);
};

$getBody = static function (): array {
    $decoded = json_decode(Flight::request()->getBody());
    if (!$decoded instanceof stdClass) {
        throw new InvalidArgumentException('Request body must be a valid JSON object.');
    }
    return (array) $decoded;
};

$authorize = static function (string $permission) use ($sendError): bool {
    try {
        return (new ApiAuthMiddleware())->authorize($permission);
    } catch (Throwable $exception) {
        $sendError($exception);
        return false;
    }
};

Flight::route('GET /api/external-search', static function () use ($sendError): void {
    try {
        $query = (string) ($_GET['q'] ?? '');
        $source = (string) ($_GET['source'] ?? 'dummyjson');
        $username = isset($_GET['username']) ? (string) $_GET['username'] : null;
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if ($page === false) {
            throw new InvalidArgumentException('Page must be an integer.');
        }

        $service = new ExternalSearchService(Database::getConfig());
        Flight::json($service->search($query, $source, $username, $page), 200);
    } catch (Throwable $exception) {
        $sendError($exception);
    }
});

Flight::route('GET /api/@entity', static function (string $entity) use ($authorize, $getApiService, $sendResult, $sendError): void {
    if (!$authorize('read')) {
        return;
    }
    try {
        if (!ApiService::supportsEntity($entity)) {
            Flight::json(['error' => 'Resource not found.'], 404);
            return;
        }
        $sendResult($getApiService()->handle($entity, 'GET', null, []));
    } catch (Throwable $exception) {
        $sendError($exception);
    }
});

Flight::route('GET /api/@entity/@id', static function (string $entity, string $id) use ($authorize, $getApiService, $sendResult, $sendError): void {
    if (!$authorize('read')) {
        return;
    }
    try {
        if (!ApiService::supportsEntity($entity)) {
            Flight::json(['error' => 'Resource not found.'], 404);
            return;
        }
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new InvalidArgumentException('Record id must be a positive integer.');
        }
        $sendResult($getApiService()->handle($entity, 'GET', (int) $id, []));
    } catch (Throwable $exception) {
        $sendError($exception);
    }
});

Flight::route('POST /api/@entity', static function (string $entity) use ($authorize, $getApiService, $getBody, $sendResult, $sendError): void {
    if (!$authorize('write')) {
        return;
    }
    try {
        if (!ApiService::supportsEntity($entity)) {
            Flight::json(['error' => 'Resource not found.'], 404);
            return;
        }
        $data = $getBody();
        $service = $getApiService();
        $sendResult($service->handle($entity, 'POST', null, $data));
    } catch (Throwable $exception) {
        $sendError($exception);
    }
});

foreach (['PUT', 'PATCH'] as $method) {
    Flight::route($method . ' /api/@entity/@id', static function (string $entity, string $id) use ($method, $authorize, $getApiService, $getBody, $sendResult, $sendError): void {
        if (!$authorize('write')) {
            return;
        }
        try {
            if (!ApiService::supportsEntity($entity)) {
                Flight::json(['error' => 'Resource not found.'], 404);
                return;
            }
            if (!ctype_digit($id) || (int) $id < 1) {
                throw new InvalidArgumentException('Record id must be a positive integer.');
            }
            $data = $getBody();
            $service = $getApiService();
            $sendResult($service->handle($entity, $method, (int) $id, $data));
        } catch (Throwable $exception) {
            $sendError($exception);
        }
    });
}

Flight::route('DELETE /api/@entity/@id', static function (string $entity, string $id) use ($authorize, $getApiService, $sendResult, $sendError): void {
    if (!$authorize('write')) {
        return;
    }
    try {
        if (!ApiService::supportsEntity($entity)) {
            Flight::json(['error' => 'Resource not found.'], 404);
            return;
        }
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new InvalidArgumentException('Record id must be a positive integer.');
        }
        $sendResult($getApiService()->handle($entity, 'DELETE', (int) $id, []));
    } catch (Throwable $exception) {
        $sendError($exception);
    }
});

Flight::map('notFound', static function () use ($sendError): void {
    Flight::json(['error' => 'Resource not found.'], 404);
});

Flight::map('error', static function (Throwable $exception) use ($sendError): void {
    $sendError($exception);
});
