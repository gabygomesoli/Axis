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
        $fromUser = null;
        if (preg_match('/\bfrom:@([A-Za-z0-9_.]+)/', $q, $m)) {
            $fromUser = $m[1];
            $q = trim(preg_replace('/\bfrom:@[A-Za-z0-9_.]+/', '', $q));
        }
        if ($fromUser) {
            $where[] = 'u.nome_usuario = ?';
            $params[] = $fromUser;
        }
        if ($q !== '') {
            $where[] = 'p.content LIKE ?';
            $params[] = '%' . $q . '%';
        }
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM posts p JOIN usuarios u ON u.id = p.user_id $whereSql");
    $stmt->execute($params);
    $total = intval($stmt->fetch()['total'] ?? 0);

    $sql = "
        SELECT p.id, p.content, p.created_at, u.nome_completo AS name, u.nome_usuario AS username, u.foto_perfil AS avatar_url,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
        FROM posts p
        JOIN usuarios u ON u.id = p.user_id
        $whereSql
        ORDER BY p.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    echo json_encode(['posts' => $posts, 'page' => $page, 'per_page' => $per_page, 'total' => $total]);
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
    $data = json_decode(file_get_contents('php://input'), true);
    $content = trim($data['content'] ?? '');

    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Conteúdo vazio.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$user['id'], $content]);
    $post_id = $pdo->lastInsertId();

    echo json_encode(['ok' => true, 'post_id' => $post_id]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
