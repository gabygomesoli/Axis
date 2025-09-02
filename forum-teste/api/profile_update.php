<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$user = require_auth();
$data = require_json();
$name = trim($data['name'] ?? '');
$avatar_url = trim($data['avatar_url'] ?? '');
$bio = trim($data['bio'] ?? '');

$stmt = $pdo->prepare('UPDATE users SET name = ?, avatar_url = ?, bio = ? WHERE id = ?');
$stmt->execute([$name ?: $_SESSION['user']['name'], $avatar_url ?: null, $bio ?: null, $user['id']]);

// Mantém sessão atualizada
$_SESSION['user']['name'] = $name ?: $_SESSION['user']['name'];

echo json_encode(['ok' => true]);
