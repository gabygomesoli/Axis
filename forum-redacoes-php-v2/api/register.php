<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$d    = require_json();
$name = trim($d['name'] ?? '');
$u    = trim($d['username'] ?? '');
$p    = $d['password'] ?? '';

if ($name === '' || $u === '' || $p === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Preencha nome, usuário e senha.']);
    exit;
}

try {
    $pdo->prepare('
        INSERT INTO users (name, username, password_hash)
        VALUES (?, ?, ?)
    ')->execute([
        $name,
        $u,
        password_hash($p, PASSWORD_DEFAULT)
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code($e->getCode() == 23000 ? 409 : 500);
    echo json_encode([
        'error' => $e->getCode() == 23000
            ? 'Usuário já existe.'
            : 'Erro ao registrar.'
    ]);
}
