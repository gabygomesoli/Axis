<?php
require_once "db.php";


if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT username, foto_perfil FROM users WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: logout.php");
    exit;
}
?>
<h2>Bem-vindo, <?php echo htmlspecialchars($usuario['username']); ?>!</h2>
<img src="<?php echo $usuario['foto_perfil'] ?: 'https://via.placeholder.com/120?text=Foto'; ?>" 
     style="width:120px;height:120px;border-radius:50%;">
<a href="logout.php">Sair</a>
