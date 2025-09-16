<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$u  = require_auth();
$d  = require_json();
$id = intval($d['essay_id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'essay_id inválido']);
    exit;
}

// Verifica se a redação existe
$ch = $pdo->prepare('SELECT id FROM essays WHERE id = ?');
$ch->execute([$id]);

if (!$ch->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Redação não encontrada']);
    exit;
}

// Tenta inserir like, se já existir remove (toggle)
try {
    $pdo->prepare('INSERT INTO essay_likes (essay_id, user_id) VALUES (?, ?)')
        ->execute([$id, $u['id']]);
} catch (PDOException $e) {
    $pdo->prepare('DELETE FROM essay_likes WHERE essay_id = ? AND user_id = ?')
        ->execute([$id, $u['id']]);
}

// Conta os likes atualizados
$c = intval(
    $pdo->query('SELECT COUNT(*) c FROM essay_likes WHERE essay_id = ' . $id)
        ->fetch()['c']
);

echo json_encode([
    'ok'         => true,
    'like_count' => $c
]);
