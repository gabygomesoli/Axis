<?php
include("config.php");

$erroEmail = "";
$erroSenha = "";
$erroConfirmar = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar = $_POST['confirmar'];
    $tipo  = $_POST['tipo'];
    $cep   = $_POST['cep'];
    $termos = isset($_POST['termos']);

    // validações
    if (empty($email)) {
        $erroEmail = "Digite um e-mail.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $erroEmail = "Este e-mail já está em uso. Tente outro.";
        }
    }

    if (empty($senha)) {
        $erroSenha = "Digite uma senha.";
    } elseif ($senha !== $confirmar) {
        $erroConfirmar = "As senhas não coincidem.";
    }

    // só cadastra se não tiver erro
    if (empty($erroEmail) && empty($erroSenha) && empty($erroConfirmar) && $tipo && $cep && $termos) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (email, senha, tipo, cep) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $senhaHash, $tipo, $cep);

        if ($stmt->execute()) {
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='index.php';</script>";
            exit;
        } else {
            $erro = "Erro ao cadastrar.";
        }
    } else {
        if (!$termos) {
            $erro = "Aceite os termos para continuar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro</title>
  <link rel="icon" type="image/png" href="img/logo.png">
  <link rel="stylesheet" href="style.css">
  <style>
    .erro {
      color: red;
      font-size: 14px;
      margin-top: -10px;
      margin-bottom: 10px;
      text-align: left;
    }
  </style>
</head>
<body>
<div class="container">
  <form method="post" class="card">
    <h2>Cadastro</h2>
    <?php if(!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>
    
    <input type="email" name="email" placeholder="E-mail" required 
           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
    <?php if(!empty($erroEmail)) echo "<p class='erro'>$erroEmail</p>"; ?>
    
    <input type="password" name="senha" placeholder="Senha" required>
    <?php if(!empty($erroSenha)) echo "<p class='erro'>$erroSenha</p>"; ?>
    
    <input type="password" name="confirmar" placeholder="Confirmar senha" required>
    <?php if(!empty($erroConfirmar)) echo "<p class='erro'>$erroConfirmar</p>"; ?>

    <select name="tipo" required>
        <option value="">Selecione o tipo</option>
        <option value="aluno" <?php if(isset($tipo) && $tipo=="aluno") echo "selected"; ?>>Aluno</option>
        <option value="professor" <?php if(isset($tipo) && $tipo=="professor") echo "selected"; ?>>Professor</option>
    </select>
    
    <input type="text" name="cep" placeholder="CEP" required 
           value="<?php echo isset($cep) ? htmlspecialchars($cep) : ''; ?>">
    
    <label>
      <input type="checkbox" name="termos" <?php if(isset($termos) && $termos) echo "checked"; ?>>
      Aceito os termos de uso
    </label>
    
    <button type="submit">Cadastrar</button>
  </form>
</div>
</body>
</html>
