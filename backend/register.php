<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

require_method('POST');

$data = read_json_body();
$username = trim((string) ($data['username'] ?? ''));
$password = (string) ($data['password'] ?? '');

if ($username === '' || strlen($username) < 3 || strlen($username) > 32) {
    fail('Username must be 3 to 32 characters long', 422);
}

if (strlen($password) < 6) {
    fail('Password must be at least 6 characters long', 422);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $statement = db()->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
    $statement->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
    ]);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fail('Username already exists', 409);
    }
    fail('Database error', 500);
}

send_json([
    'message' => 'Registration successful',
    'username' => $username,
], 201);
