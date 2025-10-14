<?php
require_once "db.php";


$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar = $_POST['confirmar'];
    $nome_completo = trim($_POST['nome_completo']);
    $tipo  = $_POST['tipo'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um email válido.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";
    } elseif ($senha !== $confirmar) {
        $erro = "As senhas não coincidem.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = "Já existe um usuário com este e-mail.";
        } else {
            $_SESSION['cadastro'] = [
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'nome_completo' => $nome_completo,
                'tipo' => $tipo
            ];
            header("Location: cadastro2.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <form method="post" class="card">
    <h2>Entre para a Axis!</h2>
    <?php if($erro) echo "<p class='erro'>$erro</p>"; ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <input type="password" name="confirmar" placeholder="Confirmar senha" required>
    <input type="text" name="nome_completo" placeholder="Nome completo" required>
    <select name="tipo" required>
      <option value="">Selecione</option>
      <option value="aluno">Aluno</option>
      <option value="professor">Professor</option>
    </select>
    <button type="submit">Avançar</button>
  </form>
</div>
</body>
</html>
