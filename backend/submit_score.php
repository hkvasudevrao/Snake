<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_method('POST');

$authUser = require_auth_user();
$data = read_json_body();
$score = $data['score'] ?? null;

if (!is_int($score)) {
    fail('Score must be an integer', 422);
}

if ($score < 0 || $score > 1000000) {
    fail('Score out of accepted range', 422);
}

$statement = db()->prepare('INSERT INTO scores (user_id, score) VALUES (:user_id, :score)');
$statement->execute([
    'user_id' => (int) $authUser['sub'],
    'score' => $score,
]);

send_json([
    'message' => 'Score submitted',
    'score' => $score,
]);
