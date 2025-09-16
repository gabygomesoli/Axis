<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link rel="icon" type="image/png" href="img/logo.png">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Bem-vindo, <?php echo $_SESSION['usuario']; ?>!</h2>
    <a href="logout.php">Sair</a>
  </div>
</div>
</body>
</html>
