<?php

require_once __DIR__ . '/../config/Database.php';

class ApiAuthMiddleware
{
    private const PERMISSIONS = [
        'reader' => ['read'],
        'admin' => ['read', 'write'],
    ];

    public function authorize(string $permission): bool
    {
        $tokens = Database::getConfig()['api_tokens'] ?? [];
        if (!is_array($tokens) || $tokens === []) {
            error_log('FarisCommerce API authentication is not configured.');
            Flight::json(['error' => 'API authentication is not configured.'], 500);
            return false;
        }

        $authorization = Flight::request()->getHeader('Authorization');
        if (!preg_match('/^Bearer\s+(\S+)$/i', trim($authorization), $matches)) {
            Flight::response()->header('WWW-Authenticate', 'Bearer');
            Flight::json(['error' => 'A valid bearer token is required.'], 401);
            return false;
        }

        $role = $this->findRole($matches[1], $tokens);
        if ($role === null) {
            Flight::response()->header('WWW-Authenticate', 'Bearer');
            Flight::json(['error' => 'A valid bearer token is required.'], 401);
            return false;
        }

        if (!in_array($permission, self::PERMISSIONS[$role] ?? [], true)) {
            Flight::json(['error' => 'This token does not have permission to perform this operation.'], 403);
            return false;
        }

        return true;
    }

    private function findRole(string $providedToken, array $tokens): ?string
    {
        foreach ($tokens as $role => $configuredTokens) {
            if (!isset(self::PERMISSIONS[$role])) {
                continue;
            }
            foreach (is_array($configuredTokens) ? $configuredTokens : [$configuredTokens] as $token) {
                if (is_string($token) && $token !== '' && hash_equals($token, $providedToken)) {
                    return $role;
                }
            }
        }

        return null;
    }
}
