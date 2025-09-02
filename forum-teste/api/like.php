<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$user = require_auth();
$data = require_json();
$post_id = intval($data['post_id'] ?? 0);
$action = ($data['action'] ?? 'toggle');

if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'post_id inválido']);
    exit;
}

// Garante que o post existe
$chk = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
$chk->execute([$post_id]);
if (!$chk->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Post não encontrado']);
    exit;
}

if ($action === 'like') {
    $stmt = $pdo->prepare('INSERT IGNORE INTO likes (post_id, user_id) VALUES (?, ?)');
    $stmt->execute([$post_id, $user['id']]);
} elseif ($action === 'unlike') {
    $stmt = $pdo->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
    $stmt->execute([$post_id, $user['id']]);
} else { // toggle
    // Tenta inserir, se já existe, deleta
    try {
        $stmt = $pdo->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
        $stmt->execute([$post_id, $user['id']]);
    } catch (PDOException $e) {
        $stmt = $pdo->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
        $stmt->execute([$post_id, $user['id']]);
    }
}

$count = $pdo->query('SELECT COUNT(*) AS c FROM likes WHERE post_id = ' . intval($post_id))->fetch()['c'];
echo json_encode(['ok' => true, 'like_count' => intval($count)]);
