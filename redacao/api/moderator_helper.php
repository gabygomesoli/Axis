<?php
require_once __DIR__ . '/../config/db.php';

function require_moderator($pdo) {
    if (!isset($_SESSION['usuario']['id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autenticado']);
        exit;
    }

    $u = $_SESSION['usuario'];
    $stmt = $pdo->prepare('SELECT tipo FROM usuarios WHERE id = ?');
    $stmt->execute([$u['id']]);
    $tipo = $stmt->fetch()['tipo'] ?? 'aluno';

    if ($tipo !== 'professor') {
        http_response_code(403);
        echo json_encode(['error' => 'Apenas professores podem alterar o status da redação']);
        exit;
    }

    return $u;
}