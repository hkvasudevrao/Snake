<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64url_decode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    return $decoded === false ? '' : $decoded;
}

function jwt_secret(): string
{
    return getenv('APP_JWT_SECRET') ?: 'replace-this-dev-secret';
}

function issue_token(int $userId, string $username): string
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'sub' => $userId,
        'username' => $username,
        'iat' => time(),
        'exp' => time() + (7 * 24 * 3600),
    ];

    $headerEncoded = base64url_encode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
    $payloadEncoded = base64url_encode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
    $unsigned = $headerEncoded . '.' . $payloadEncoded;
    $signature = hash_hmac('sha256', $unsigned, jwt_secret(), true);

    return $unsigned . '.' . base64url_encode($signature);
}

function verify_token(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$headerEncoded, $payloadEncoded, $providedSignature] = $parts;
    $unsigned = $headerEncoded . '.' . $payloadEncoded;
    $expectedSignature = base64url_encode(hash_hmac('sha256', $unsigned, jwt_secret(), true));

    if (!hash_equals($expectedSignature, $providedSignature)) {
        return null;
    }

    $payloadRaw = base64url_decode($payloadEncoded);
    $payload = json_decode($payloadRaw, true);
    if (!is_array($payload)) {
        return null;
    }

    $exp = $payload['exp'] ?? 0;
    if (!is_int($exp) || $exp < time()) {
        return null;
    }

    return $payload;
}

function authorization_header(): string
{
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strtolower((string) $name) === 'authorization') {
                return trim((string) $value);
            }
        }
    }

    return trim((string) ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
}

function require_auth_user(): array
{
    $auth = authorization_header();

    if (!preg_match('/^Bearer\s+(\S+)$/', $auth, $matches)) {
        fail('Missing bearer token', 401);
    }

    $payload = verify_token($matches[1]);
    if ($payload === null) {
        fail('Invalid or expired token', 401);
    }

    return $payload;
}
