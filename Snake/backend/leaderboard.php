<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

$limit = (int) ($_GET['limit'] ?? 10);
$limit = max(1, min(100, $limit));

$statement = db()->prepare(
    'SELECT u.username, MAX(s.score) AS best_score
     FROM scores s
     INNER JOIN users u ON u.id = s.user_id
     GROUP BY s.user_id, u.username
     ORDER BY best_score DESC, u.username ASC
     LIMIT :limit'
);
$statement->bindValue(':limit', $limit, PDO::PARAM_INT);
$statement->execute();

$leaderboard = $statement->fetchAll();

send_json([
    'leaderboard' => $leaderboard,
]);
