<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$user = require_auth();
$data = require_json();

$redacao_id = intval($data['redacao_id'] ?? 0);

if ($redacao_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da redação inválido.']);
    exit;
}

$chk = $pdo->prepare('SELECT id FROM redacoes WHERE id = ?');
$chk->execute([$redacao_id]);
if (!$chk->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Redação não encontrada.']);
    exit;
}

try {
    $ins = $pdo->prepare('INSERT INTO likes_redacoes (redacao_id, user_id) VALUES (?, ?)');
    $ins->execute([$redacao_id, $user['id']]);
} catch (PDOException $e) {
    $del = $pdo->prepare('DELETE FROM likes_redacoes WHERE redacao_id = ? AND user_id = ?');
    $del->execute([$redacao_id, $user['id']]);
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM likes_redacoes WHERE redacao_id = ?');
$stmt->execute([$redacao_id]);
$count = intval($stmt->fetchColumn());

echo json_encode(['ok' => true, 'like_count' => $count]);