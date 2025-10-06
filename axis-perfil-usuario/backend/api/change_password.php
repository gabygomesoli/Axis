<?php
require_once __DIR__.'/../config.php';
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$user_id = intval($body['user_id'] ?? 0);
$current = strval($body['current'] ?? '');
$new = strval($body['new'] ?? '');

if (!$user_id || !$current || !$new) json_out(['error'=>'missing fields'], 400);

$stmt = db()->prepare('SELECT password_hash FROM users WHERE id=?');
$stmt->execute([$user_id]); $row = $stmt->fetch();
if (!$row) json_out(['error'=>'user not found'], 404);

if (!password_verify($current, $row['password_hash'])) json_out(['error'=>'current password invalid'], 400);

$newHash = password_hash($new, PASSWORD_BCRYPT);
$up = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
$up->execute([$newHash, $user_id]);
json_out(['ok'=>true]);
