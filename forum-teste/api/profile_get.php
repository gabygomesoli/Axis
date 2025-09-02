<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$user = require_auth();

$stmt = $pdo->prepare('SELECT id, name, username, avatar_url, bio, created_at FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
echo json_encode(['profile' => $stmt->fetch()]);
