<?php

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestedFile = __DIR__ . (string) $requestPath;
    if ($requestPath !== '/' && is_file($requestedFile)) {
        return false;
    }
}

require __DIR__ . '/routes/api.php';