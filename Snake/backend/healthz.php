<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

try {
    db()->query('SELECT 1');
    send_json([
        'status' => 'ok',
        'database' => 'reachable',
    ]);
} catch (Throwable $exception) {
    fail('Database unavailable', 503);
}
