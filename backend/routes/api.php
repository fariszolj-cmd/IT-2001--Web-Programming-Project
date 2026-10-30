<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/ApiService.php';

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$segments = array_values(array_filter(explode('/', trim((string) $path, '/')), 'strlen'));
$apiIndex = array_search('api', $segments, true);

if ($apiIndex === false || count($segments) < $apiIndex + 2 || count($segments) > $apiIndex + 3) {
    http_response_code(404);
    echo json_encode(['error' => 'Use /api/{entity} or /api/{entity}/{id}.']);
    return;
}

$entity = $segments[$apiIndex + 1];
$id = null;
if (isset($segments[$apiIndex + 2])) {
    if (!ctype_digit($segments[$apiIndex + 2]) || (int) $segments[$apiIndex + 2] < 1) {
        http_response_code(400);
        echo json_encode(['error' => 'Record id must be a positive integer.']);
        return;
    }
    $id = (int) $segments[$apiIndex + 2];
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$data = [];
if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
    $rawBody = file_get_contents('php://input');
    $decoded = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        http_response_code(400);
        echo json_encode(['error' => 'Request body must be a valid JSON object.']);
        return;
    }
    $data = $decoded;
}

try {
    $service = new ApiService(Database::getConnection());
    $result = $service->handle($entity, $method, $id, $data);
    http_response_code($result['status']);
    if ($result['status'] !== 204) {
        echo json_encode($result['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);
} catch (PDOException $exception) {
    $sqlState = (string) $exception->getCode();
    if ($sqlState === '23000') {
        http_response_code(409);
        echo json_encode(['error' => 'The operation conflicts with an existing record or relationship.']);
    } elseif (strpos($sqlState, '22') === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'One or more field values are invalid.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'A database error occurred.']);
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'The server could not complete the request.']);
}