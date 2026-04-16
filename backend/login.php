<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_method('POST');

$data = read_json_body();
$username = trim((string) ($data['username'] ?? ''));
$password = (string) ($data['password'] ?? '');

if ($username === '' || $password === '') {
    fail('Username and password are required', 422);
}

$statement = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
$statement->execute(['username' => $username]);
$user = $statement->fetch();

if (!$user || !password_verify($password, (string) $user['password_hash'])) {
    fail('Invalid username or password', 401);
}

$token = issue_token((int) $user['id'], (string) $user['username']);

send_json([
    'token' => $token,
    'username' => $user['username'],
]);
