<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

require_once __DIR__ . '/../../autenticar/config.php';

$userId = (int) $_SESSION['usuario']['id'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];

$current = $body['current_password'] ?? '';
$new     = $body['new_password'] ?? '';

if ($current === '' || $new === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Preencha todos os campos.']);
    exit;
}

if (strlen($new) < 6) {
    http_response_code(422);
    echo json_encode(['error' => 'A nova senha deve ter pelo menos 6 caracteres.']);
    exit;
}

$sql = "SELECT senha, auth_provider FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuário não encontrado.']);
    exit;
}

if (!empty($user['auth_provider']) && $user['auth_provider'] !== 'local') {
    http_response_code(400);
    echo json_encode(['error' => 'Esta conta utiliza login pelo Google. A senha não pode ser alterada aqui.']);
    exit;
}

if (!password_verify($current, $user['senha'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Senha atual incorreta.']);
    exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);

$up = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
$up->bind_param('si', $newHash, $userId);
$up->execute();

echo json_encode(['ok' => true]);