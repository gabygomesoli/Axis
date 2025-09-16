<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$d = require_json();
$u = trim($d['username'] ?? '');
$p = $d['password'] ?? '';

if ($u === '' || $p === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Informe usuário e senha.']);
    exit;
}

$s = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$s->execute([$u]);
$user = $s->fetch();

if (!$user || !password_verify($p, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciais inválidas.']);
    exit;
}

$_SESSION['user'] = [
    'id'         => $user['id'],
    'name'       => $user['name'],
    'username'   => $user['username'],
    'avatar_url' => $user['avatar_url']
];

echo json_encode([
    'ok'   => true,
    'user' => $_SESSION['user']
]);
