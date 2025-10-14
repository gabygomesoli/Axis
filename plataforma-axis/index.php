<?php
require_once "db.php";


$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if ($email && $senha) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['password_hash'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nome_usuario'] = $user['username'];
            $_SESSION['foto_perfil'] = $user['foto_perfil'];
            header("Location: home.php");
            exit;
        } else {
            $erro = "Email ou senha incorretos.";
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <form method="post" class="card">
    <h2>Seja bem-vindo!</h2>
    <?php if($erro) echo "<p class='erro'>$erro</p>"; ?>
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Entrar</button>
    <div class="separator"><span>ou</span></div>
    <button type="button" onclick="window.location.href='cadastro1.php'">Cadastre-se</button>
  </form>
</div>
</body>
</html>


