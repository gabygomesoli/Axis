<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$m = $_SERVER['REQUEST_METHOD'];

if ($m === 'GET') {
    $page = max(1, intval($_GET['page'] ?? 1));
    $per = min(50, max(1, intval($_GET['per_page'] ?? 10)));
    $off = ($page - 1) * $per;
    $q = trim($_GET['q'] ?? '');

    $where = [];
    $params = [];

    if ($q !== '') {
        $from = null;

        if (preg_match('/\bfrom:@([A-Za-z0-9_.]+)/', $q, $mm)) {
            $from = $mm[1];
            $q = trim(preg_replace('/\bfrom:@[A-Za-z0-9_.]+/', '', $q));
        }

        if ($from) {
            $where[] = 'u.username = ?';
            $params[] = $from;
        }

        if ($q !== '') {
            $where[] = '(e.title LIKE ? OR e.content LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
    }

    // Visibilidade por status: público vê apenas 'publicada'; autor vê suas próprias; moderador vê tudo
    $extra = '';
    $viewer_id = isset($_SESSION['user']) ? intval($_SESSION['user']['id']) : 0;
    $is_moderator = false;

    if ($viewer_id) {
        $rl = $pdo->query('SELECT role FROM users WHERE id=' . $viewer_id)->fetch()['role'] ?? 'user';
        $is_moderator = in_array($rl, ['moderator', 'admin']);
    }

    if (!$is_moderator) {
        $where[] = '(e.status = "publicada" OR e.user_id = ' . $viewer_id . ')';
    }

    $w = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $s = $pdo->prepare("SELECT COUNT(*) AS t FROM essays e JOIN users u ON u.id = e.user_id $w");
    $s->execute($params);
    $total = intval($s->fetch()['t'] ?? 0);

    $s = $pdo->prepare("
        SELECT 
            e.id, e.title, e.content, e.created_at, e.status, e.scan_path,
            u.name, u.username, u.avatar_url
        FROM essays e
        JOIN users u ON u.id = e.user_id
        $w
        ORDER BY e.created_at DESC
        LIMIT $per OFFSET $off
    ");
    $s->execute($params);
    $rows = $s->fetchAll();

    $liked = [];
    if (isset($_SESSION['user'])) {
        $uid = intval($_SESSION['user']['id']);
        foreach ($pdo->query("SELECT essay_id FROM essay_likes WHERE user_id = $uid") as $r) {
            $liked[intval($r['essay_id'])] = true;
        }
    }

    foreach ($rows as &$r) {
        $eid = intval($r['id']);
        $r['like_count'] = intval($pdo->query("SELECT COUNT(*) c FROM essay_likes WHERE essay_id = $eid")->fetch()['c']);
        $r['comment_count'] = intval($pdo->query("SELECT COUNT(*) c FROM essay_comments WHERE essay_id = $eid")->fetch()['c']);
        $r['liked_by_me'] = isset($liked[$eid]);
    }

    echo json_encode([
        'essays'    => $rows,
        'page'      => $page,
        'per_page'  => $per,
        'total'     => $total
    ]);
    exit;
}

if ($m === 'POST') {
    $user = require_auth();
    $d = require_json();

    $title = trim($d['title'] ?? '');
    $content = rtrim($d['content'] ?? '');

    if ($title === '' || $content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Informe título e conteúdo.']);
        exit;
    }

    $MAXC = 66;
    $MAXL = 30;
    $lines = preg_split('/\r?\n/', $content);
    $norm = [];

    foreach ($lines as $ln) {
        $ln = preg_replace('/\t/', '    ', $ln);

        while (mb_strlen($ln) > $MAXC) {
            $norm[] = mb_substr($ln, 0, $MAXC);
            $ln = mb_substr($ln, $MAXC);
            if (count($norm) >= $MAXL) break 2;
        }

        $norm[] = $ln;
        if (count($norm) >= $MAXL) break;
    }

    $final = implode("\n", array_slice($norm, 0, $MAXL));

    $pdo->prepare('INSERT INTO essays (user_id, title, content) VALUES (?, ?, ?)')
        ->execute([$user['id'], $title, $final]);

    echo json_encode([
        'ok'       => true,
        'essay_id' => $pdo->lastInsertId()
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
