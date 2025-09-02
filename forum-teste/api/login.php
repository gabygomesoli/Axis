<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$data = require_json();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Informe usuário e senha.']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciais inválidas.']);
    exit;
}

$_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'username' => $user['username']];
echo json_encode(['ok' => true, 'user' => $_SESSION['user']]);
