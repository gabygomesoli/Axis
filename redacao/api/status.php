<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/moderator_helper.php';

$u = require_moderator($pdo);
$data = require_json();

$redacao_id = intval($data['redacao_id'] ?? 0);
$status     = $data['status'] ?? '';

$allowed = ['em_correcao','corrigida','publicada'];
if ($redacao_id <= 0 || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

$stmt = $pdo->prepare('UPDATE redacoes SET status = ? WHERE id = ?');
$stmt->execute([$status, $redacao_id]);

echo json_encode(['ok' => true]);
