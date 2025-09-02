<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$user = require_auth();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $only_unread = intval($_GET['unread'] ?? 0) === 1;
    $where = 'WHERE n.user_id = ?';
    if ($only_unread) $where .= ' AND n.is_read = 0';

    $stmt = $pdo->prepare("
        SELECT n.id, n.type, n.source_post_id, n.source_comment_id, n.is_read, n.created_at,
               a.username AS actor_username, a.name AS actor_name, a.avatar_url AS actor_avatar
        FROM notifications n
        JOIN users a ON a.id = n.actor_user_id
        $where
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user['id']]);
    // contagem não lidas
    $c = $pdo->prepare('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0');
    $c->execute([$user['id']]);
    $count_unread = intval($c->fetch()['c'] ?? 0);
    echo json_encode(['notifications' => $stmt->fetchAll(), 'unread' => $count_unread]);
    exit;
}

if ($method === 'POST') {
    $data = require_json();
    $action = $data['action'] ?? 'mark_read_all';
    if ($action === 'mark_read_all') {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$user['id']]);
        echo json_encode(['ok' => true]);
        exit;
    } elseif ($action === 'mark_read' && !empty($data['ids']) && is_array($data['ids'])) {
        $in = implode(',', array_map('intval', $data['ids']));
        $pdo->query("UPDATE notifications SET is_read = 1 WHERE user_id = {$user['id']} AND id IN ($in)");
        echo json_encode(['ok' => true]);
        exit;
    }
    http_response_code(400);
    echo json_encode(['error' => 'Ação inválida']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
