<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/mentions_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(1, intval($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $per_page;
    $q = trim($_GET['q'] ?? '');

    $where = [];
    $params = [];

    if ($q !== '') {
        // sintaxe simples: from:@username (filtra por autor) + texto livre
        $fromUser = null;
        if (preg_match('/\bfrom:@([A-Za-z0-9_.]+)/', $q, $m)) {
            $fromUser = $m[1];
            $q = trim(preg_replace('/\bfrom:@[A-Za-z0-9_.]+/', '', $q));
        }
        if ($fromUser) {
            $where[] = 'u.username = ?';
            $params[] = $fromUser;
        }
        if ($q !== '') {
            $where[] = 'p.content LIKE ?';
            $params[] = '%' . $q . '%';
        }
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // total
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM posts p JOIN users u ON u.id = p.user_id $whereSql");
    $stmt->execute($params);
    $total = intval($stmt->fetch()['total'] ?? 0);

    // dados
    $sql = "
        SELECT p.id, p.content, p.created_at, u.name, u.username, u.avatar_url,
               (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
               (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
        FROM posts p
        JOIN users u ON u.id = p.user_id
        $whereSql
        ORDER BY p.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    $liked_map = [];
    if (isset($_SESSION['user'])) {
        $uid = $_SESSION['user']['id'];
        $in = $pdo->query("SELECT post_id FROM likes WHERE user_id = $uid")->fetchAll();
        foreach ($in as $row) { $liked_map[$row['post_id']] = true; }
    }
    foreach ($posts as &$p) {
        $p['liked_by_me'] = isset($liked_map[$p['id']]);
    }
    echo json_encode(['posts' => $posts, 'page' => $page, 'per_page' => $per_page, 'total' => $total]);
    exit;
}

if ($method === 'POST') {
    $user = require_auth();
    $data = require_json();
    $content = trim($data['content'] ?? '');
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Conteúdo vazio.']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, content) VALUES (?, ?)');
    $stmt->execute([$user['id'], $content]);
    $post_id = $pdo->lastInsertId();

    // cria notificações de menção
    create_notifications_for_mentions($pdo, $user['id'], $content, $post_id, null);

    echo json_encode(['ok' => true, 'post_id' => $post_id]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
