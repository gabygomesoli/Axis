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

$nome_completo = trim($body['nome_completo'] ?? '');
$nome_usuario  = trim($body['nome_usuario']  ?? '');
$tipo          = trim($body['tipo']          ?? '');
$cep           = trim($body['cep']           ?? '');
$street        = trim($body['street']        ?? '');
$number        = trim($body['number']        ?? '');
$city          = trim($body['city']          ?? '');

$errors = [];

if ($nome_completo !== '' || $nome_usuario !== '' || $tipo !== '') {

    if ($nome_completo === '') {
        $errors[] = 'Informe o nome completo.';
    }
    if ($nome_usuario === '') {
        $errors[] = 'Informe o nome de usuário.';
    } else {
        $sql = "SELECT id FROM usuarios WHERE nome_usuario = ? AND id <> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $nome_usuario, $userId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Nome de usuário já está em uso.';
        }
    }

    if ($tipo !== 'aluno' && $tipo !== 'professor') {
        $errors[] = 'Tipo de usuário inválido.';
    }
}

if ($cep !== '') {
    $cepDigitos = preg_replace('/\D/', '', $cep);
    if (!preg_match('/^\d{8}$/', $cepDigitos)) {
        $errors[] = 'CEP inválido.';
    } else {
        $cep = $cepDigitos;
    }
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['error' => implode(' ', $errors)]);
    exit;
}

$campos = [];
$params = [];
$tipos  = '';

if ($nome_completo !== '') { $campos[] = 'nome_completo = ?'; $params[] = $nome_completo; $tipos .= 's'; }
if ($nome_usuario  !== '') { $campos[] = 'nome_usuario  = ?'; $params[] = $nome_usuario;  $tipos .= 's'; }
if ($tipo          !== '') { $campos[] = 'tipo          = ?'; $params[] = $tipo;          $tipos .= 's'; }
if ($cep           !== '') { $campos[] = 'cep           = ?'; $params[] = $cep;           $tipos .= 's'; }

if ($campos) {
    $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
    $params[] = $userId;
    $tipos   .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();

    // Atualiza sessão
    if ($nome_completo !== '') $_SESSION['usuario']['nome'] = $nome_completo;
    if ($nome_usuario  !== '') $_SESSION['usuario']['nome_usuario'] = $nome_usuario;
}

echo json_encode(['ok' => true]);