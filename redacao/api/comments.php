<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $redacao_id = intval($_GET['redacao_id'] ?? 0);
    if ($redacao_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'redacao_id inválido']);
        exit;
    }

    $stmt = $pdo->prepare('
        SELECT 
            c.id,
            c.comentario AS content,
            c.created_at,
            u.nome_completo AS name,
            u.nome_usuario  AS username,
            u.foto_perfil   AS avatar_url
        FROM comentarios_redacoes c
        JOIN usuarios u ON u.id = c.user_id
        WHERE c.redacao_id = ?
        ORDER BY c.created_at ASC
    ');
    $stmt->execute([$redacao_id]);

    echo json_encode(['comments' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $user = require_auth();
    $data = require_json();

    $redacao_id = intval($data['redacao_id'] ?? 0);
    $content    = trim($data['content'] ?? '');

    if ($redacao_id <= 0 || $content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos']);
        exit;
    }

    $chk = $pdo->prepare('SELECT id FROM redacoes WHERE id = ?');
    $chk->execute([$redacao_id]);
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Redação não encontrada']);
        exit;
    }

    $stmt = $pdo->prepare('
        INSERT INTO comentarios_redacoes (redacao_id, user_id, comentario)
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$redacao_id, $user['id'], $content]);

    echo json_encode(['ok' => true, 'comment_id' => $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);