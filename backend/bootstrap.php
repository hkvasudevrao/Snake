<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$allowedOrigin = getenv('CORS_ALLOW_ORIGIN') ?: '*';
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $exception): void {
    error_log($exception->__toString());

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    http_response_code(500);
    echo json_encode(['error' => 'Internal server error'], JSON_UNESCAPED_SLASHES);
    exit;
});

function send_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(string $message, int $status = 400, array $extra = []): void
{
    send_json(array_merge(['error' => $message], $extra), $status);
}

function require_method(string $method): void
{
    $actual = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (strtoupper($actual) !== strtoupper($method)) {
        fail('Method not allowed', 405);
    }
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        fail('Invalid JSON payload', 400);
    }

    return $decoded;
}
