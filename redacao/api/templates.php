<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $s = $pdo->prepare('SELECT * FROM redacao_templates WHERE id = ?');
        $s->execute([$id]);
        echo json_encode(['template' => $s->fetch()]);
        exit;
    }

    $s = $pdo->query('
        SELECT id, title, LEFT(prompt, 200) AS prompt_preview
        FROM redacao_templates
        ORDER BY created_at DESC
    ');
    echo json_encode(['templates' => $s->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $user = require_auth();
    $d = require_json();

    $title      = trim($d['title'] ?? '');
    $prompt     = trim($d['prompt'] ?? '');
    $collection = trim($d['collection_text'] ?? '');

    if ($title === '' || $prompt === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Título e proposta são obrigatórios']);
        exit;
    }

    $stmt = $pdo->prepare('
        INSERT INTO redacao_templates (title, prompt, collection_text)
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$title, $prompt, $collection ?: null]);

    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não suportado']);
