<?php

declare(strict_types=1);

header('Content-Type: application/json');

http_response_code(200);

echo json_encode([
    'name' => 'snake-api',
    'status' => 'ok',
    'endpoints' => [
        '/api/healthz.php',
        '/api/register.php',
        '/api/login.php',
        '/api/submit_score.php',
        '/api/leaderboard.php',
        '/api/metrics.php',
    ],
], JSON_PRETTY_PRINT);
