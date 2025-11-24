<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

$user = $_SESSION['usuario'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT n.id, n.type, n.source_post_id, n.source_comment_id, n.is_read, n.created_at,
               a.nome_usuario AS actor_username, a.nome_completo AS actor_name, a.foto_perfil AS actor_avatar
        FROM notifications n
        JOIN usuarios a ON a.id = n.actor_user_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll();

    // Conta não lidas
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $countStmt->execute([$user['id']]);
    $unread = intval($countStmt->fetchColumn() ?? 0);

    echo json_encode(['notifications' => $notifications, 'unread' => $unread]);
    exit;
}

if ($method === 'POST') {
    $data = require_json();
    $action = $data['action'] ?? 'mark_read_all';

    if ($action === 'mark_read_all') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Ação inválida']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
