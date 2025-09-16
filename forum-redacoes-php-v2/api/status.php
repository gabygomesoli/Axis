<?php
    header('Content-Type: application/json');
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/moderator_helper.php';

    $u = require_moderator($pdo);
    $data = require_json();
    $essay_id = intval($data['essay_id'] ?? 0);
    $status = $data['status'] ?? '';

    $allowed = ['em_correcao','corrigida','publicada'];
    if ($essay_id <= 0 || !in_array($status, $allowed)){ http_response_code(400); echo json_encode(['error'=>'Dados inválidos']); exit; }

    $stmt = $pdo->prepare('UPDATE essays SET status = ? WHERE id = ?');
    $stmt->execute([$status, $essay_id]);
    echo json_encode(['ok'=>true]);
