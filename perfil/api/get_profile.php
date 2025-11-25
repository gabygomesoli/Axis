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

$sql = "SELECT id, nome_completo, nome_usuario, email, tipo, cep, foto_perfil, criado_em 
        FROM usuarios WHERE id = ?";
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

$cep = preg_replace('/\D/', '', $user['cep'] ?? '');
if (strlen($cep) === 8) {
    $user['cep_formatado'] = substr($cep,0,5) . '-' . substr($cep,5);
} else {
    $user['cep_formatado'] = $user['cep'];
}

$dt = new DateTime($user['criado_em']);
$user['criado_em_br'] = $dt->format('d/m/Y');

echo json_encode(['user' => $user]);