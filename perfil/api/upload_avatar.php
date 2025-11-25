<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

if (!isset($_FILES['avatar'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Arquivo não enviado.']);
    exit;
}

require_once __DIR__ . '/../../autenticar/config.php';

$userId = (int) $_SESSION['usuario']['id'];

$file = $_FILES['avatar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Erro no upload do arquivo.']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg','jpeg','png','webp'];

if (!in_array($ext, $permitidas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato inválido. Use JPG, JPEG, PNG ou WEBP.']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'A imagem deve ter no máximo 2MB.']);
    exit;
}

$basePath  = realpath(__DIR__ . '/..');
$basePath  = realpath($basePath . '/..');

$uploadDir = $basePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfis';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$novoNome   = 'perfil_' . $userId . '_' . time() . '.' . $ext;
$destinoAbs = $uploadDir . DIRECTORY_SEPARATOR . $novoNome;

$relPath = 'uploads/perfis/' . $novoNome;

if (!move_uploaded_file($file['tmp_name'], $destinoAbs)) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao salvar o arquivo.']);
    exit;
}

$stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
$stmt->bind_param('si', $relPath, $userId);
$stmt->execute();

$_SESSION['usuario']['foto_perfil'] = $relPath;

echo json_encode([
    'ok'          => true,
    'foto_perfil' => $relPath
]);