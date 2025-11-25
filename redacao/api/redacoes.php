<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function normalize_almaco(string $content): string {
    $MAXC = 66;
    $MAXL = 30;

    $lines = preg_split('/\r?\n/', $content);
    $norm  = [];

    foreach ($lines as $ln) {
        $ln = preg_replace('/\t/', '    ', $ln);

        while (mb_strlen($ln) > $MAXC) {
            $norm[] = mb_substr($ln, 0, $MAXC);
            $ln     = mb_substr($ln, $MAXC);
            if (count($norm) >= $MAXL) {
                break 2;
            }
        }

        $norm[] = $ln;
        if (count($norm) >= $MAXL) {
            break;
        }
    }

    return implode("\n", array_slice($norm, 0, $MAXL));
}

if ($method === 'GET') {
    $page     = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(1, intval($_GET['per_page'] ?? 10)));
    $offset   = ($page - 1) * $per_page;
    $q        = trim($_GET['q'] ?? '');

    $where  = [];
    $params = [];

    if ($q !== '') {
        $fromUser = null;

        if (preg_match('/\bfrom:@([A-Za-z0-9_.]+)/', $q, $m)) {
            $fromUser = $m[1];
            $q = trim(preg_replace('/\bfrom:@[A-Za-z0-9_.]+/', '', $q));
        }

        if ($fromUser) {
            $where[]  = 'u.nome_usuario = ?';
            $params[] = $fromUser;
        }

        if ($q !== '') {
            $where[]  = '(r.titulo LIKE ? OR r.texto LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
    }

    $where[] = 'r.status = "publicada"';

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM redacoes r
        JOIN usuarios u ON u.id = r.user_id
        $whereSql
    ");
    $stmt->execute($params);
    $total = intval($stmt->fetch()['total'] ?? 0);

    $sql = "
        SELECT 
            r.id,
            r.titulo,
            r.texto,
            r.status,
            r.scan_path,
            r.created_at,
            u.nome_completo AS name,
            u.nome_usuario  AS username,
            u.foto_perfil   AS avatar_url
        FROM redacoes r
        JOIN usuarios u ON u.id = r.user_id
        $whereSql
        ORDER BY r.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $liked = [];
    $viewer_id = isset($_SESSION['usuario']['id']) ? intval($_SESSION['usuario']['id']) : 0;
    if ($viewer_id) {
        $lk = $pdo->prepare("SELECT redacao_id FROM likes_redacoes WHERE user_id = ?");
        $lk->execute([$viewer_id]);
        foreach ($lk as $r) {
            $liked[intval($r['redacao_id'])] = true;
        }
    }

    foreach ($rows as &$r) {
        $id = intval($r['id']);
        $r['like_count'] = intval(
            $pdo->query("SELECT COUNT(*) FROM likes_redacoes WHERE redacao_id = $id")
                ->fetchColumn()
        );
        $r['comment_count'] = intval(
            $pdo->query("SELECT COUNT(*) FROM comentarios_redacoes WHERE redacao_id = $id")
                ->fetchColumn()
        );
        $r['liked_by_me'] = isset($liked[$id]);
    }

    echo json_encode([
        'redacoes' => $rows,
        'page'     => $page,
        'per_page' => $per_page,
        'total'    => $total
    ]);
    exit;
}

if ($method === 'POST') {
    $user = require_auth();
    $data = require_json();

    $titulo = trim($data['titulo'] ?? '');
    $texto  = rtrim($data['texto'] ?? '');

    if ($titulo === '' || $texto === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Informe título e texto da redação.']);
        exit;
    }

    $final = normalize_almaco($texto);

    $stmt = $pdo->prepare("
        INSERT INTO redacoes (user_id, titulo, texto)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user['id'], $titulo, $final]);

    echo json_encode([
        'ok'         => true,
        'redacao_id' => $pdo->lastInsertId()
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);