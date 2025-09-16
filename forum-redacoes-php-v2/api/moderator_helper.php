<?php
function require_moderator($pdo){
    if(!isset($_SESSION['user'])){ http_response_code(401); echo json_encode(['error'=>'Não autenticado']); exit; }
    $u = $_SESSION['user'];
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$u['id']]);
    $role = $stmt->fetch()['role'] ?? 'user';
    if ($role !== 'moderator' && $role !== 'admin'){
        http_response_code(403);
        echo json_encode(['error'=>'Acesso negado: moderador necessário']);
        exit;
    }
    return $u;
}
?>