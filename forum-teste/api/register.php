<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$data = require_json();
$name = trim($data['name'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($name === '' || $username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Preencha nome, usuário e senha.']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO users (name, username, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['error' => 'Usuário já existe.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao registrar.']);
    }
}
