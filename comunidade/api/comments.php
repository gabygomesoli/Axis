<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/mentions_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $post_id = intval($_GET['post_id'] ?? 0);
    if ($post_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'post_id inválido']);
        exit;
    }
    $stmt = $pdo->prepare('
        SELECT c.id, c.content, c.created_at, u.nome_completo AS name, u.nome_usuario AS username, u.foto_perfil AS avatar_url
        FROM comments c
        JOIN usuarios u ON u.id = c.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ');
    $stmt->execute([$post_id]);
    echo json_encode(['comments' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['usuario']['id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autenticado.']);
        exit;
    }

    $user = $_SESSION['usuario'];
    $data = require_json();
    $post_id = intval($data['post_id'] ?? 0);
    $content = trim($data['content'] ?? '');
    if ($post_id <= 0 || $content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$post_id, $user['id'], $content]);
    $comment_id = $pdo->lastInsertId();

    create_notifications_for_mentions($pdo, $user['id'], $content, $post_id, $comment_id);
    echo json_encode(['ok' => true, 'comment_id' => $comment_id]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
