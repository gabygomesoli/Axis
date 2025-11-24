<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

try {
    if (!isset($_SESSION['usuario']['id'])) {
        echo json_encode(['error' => 'Não autenticado.']);
        exit;
    }

    $user = $_SESSION['usuario'];
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = intval($data['post_id'] ?? 0);

    if ($post_id <= 0) {
        echo json_encode(['error' => 'ID do post inválido.']);
        exit;
    }

    $chk = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $chk->execute([$post_id, $user['id']]);
    $like = $chk->fetch();

    if ($like) {
        $del = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $del->execute([$post_id, $user['id']]);
    } else {
        $ins = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $ins->execute([$post_id, $user['id']]);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $count = $stmt->fetchColumn();

    echo json_encode(['ok' => true, 'like_count' => $count]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
