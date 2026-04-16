<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: text/plain; version=0.0.4; charset=utf-8');

$userCount = 0;
$scoreCount = 0;
$topScore = 0;
$dbUp = 0;

try {
    $pdo = db();
    $dbUp = 1;

    $userCount = (int) $pdo->query('SELECT COUNT(*) AS value FROM users')->fetch()['value'];
    $scoreCount = (int) $pdo->query('SELECT COUNT(*) AS value FROM scores')->fetch()['value'];
    $topScore = (int) $pdo->query('SELECT COALESCE(MAX(score), 0) AS value FROM scores')->fetch()['value'];
} catch (Throwable $exception) {
    $dbUp = 0;
}

echo "snake_db_up {$dbUp}\n";
echo "snake_users_total {$userCount}\n";
echo "snake_scores_total {$scoreCount}\n";
echo "snake_top_score {$topScore}\n";
