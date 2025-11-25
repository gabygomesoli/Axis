<?php
session_start();
include("config.php");

$erro = $erroEmail = $erroSenha = $erroConfirmar = $erroNomeCompleto = $erroTipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_completo = trim($_POST['nome_completo'] ?? "");
    $email         = trim($_POST['email'] ?? "");
    $senha         = $_POST['senha'] ?? "";
    $confirmar     = $_POST['confirmar'] ?? "";
    $tipo          = $_POST['tipo'] ?? "";

    if ($nome_completo === "") {
        $erroNomeCompleto = "Digite seu nome completo.";
    }

    if ($email === "") {
        $erroEmail = "Digite um e-mail.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erroEmail = "Digite um e-mail válido.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $erroEmail = "Este e-mail já está em uso.";
        }
    }

    if ($senha === "") {
        $erroSenha = "Digite uma senha.";
    } elseif (strlen($senha) < 6) {
        $erroSenha = "A senha deve ter pelo menos 6 caracteres.";
    }

    if ($confirmar === "") {
        $erroConfirmar = "Confirme sua senha.";
    } elseif ($senha !== $confirmar) {
        $erroConfirmar = "As senhas não coincidem.";
    }

    if ($tipo === "") {
        $erroTipo = "Selecione o tipo de usuário.";
    }

    if (
        empty($erroNomeCompleto) &&
        empty($erroEmail) &&
        empty($erroSenha) &&
        empty($erroConfirmar) &&
        empty($erroTipo)
    ) {
        $_SESSION['cadastro_temp'] = [
            'nome_completo' => $nome_completo,
            'email'         => $email,
            'senha_hash'    => password_hash($senha, PASSWORD_DEFAULT),
            'tipo'          => $tipo
        ];

        header("Location: cadastro-etapa2.php");
        exit;
    } else {
        $erro = "Por favor, corrija os erros abaixo.";
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cadastro - Axis (Etapa 1)</title>
  <link rel="stylesheet" href="style.css?v=5">
  <link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="content">
        <form method="post" novalidate>
          <h2>Entre para a Axis!</h2>
          <h4>Cadastre-se e ache o eixo que faltava para seus estudos!</h4>

          <?php if (!empty($erro)): ?>
            <p class="erro erro-geral"><?php echo htmlspecialchars($erro); ?></p>
          <?php endif; ?>

          <input type="text" name="nome_completo" placeholder="nome completo" required
                 value="<?php echo htmlspecialchars($_POST['nome_completo'] ?? ''); ?>"
                 class="<?php echo !empty($erroNomeCompleto) ? 'error' : ''; ?>">
          <?php if (!empty($erroNomeCompleto)) echo "<p class='erro'>".htmlspecialchars($erroNomeCompleto)."</p>"; ?>

          <input type="email" name="email" placeholder="email" required
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                 class="<?php echo !empty($erroEmail) ? 'error' : ''; ?>">
          <?php if (!empty($erroEmail)) echo "<p class='erro'>".htmlspecialchars($erroEmail)."</p>"; ?>

          <input type="password" name="senha" placeholder="senha" required
                 class="<?php echo !empty($erroSenha) ? 'error' : ''; ?>">
          <?php if (!empty($erroSenha)) echo "<p class='erro'>".htmlspecialchars($erroSenha)."</p>"; ?>

          <input type="password" name="confirmar" placeholder="confirmar senha" required
                 class="<?php echo !empty($erroConfirmar) ? 'error' : ''; ?>">
          <?php if (!empty($erroConfirmar)) echo "<p class='erro'>".htmlspecialchars($erroConfirmar)."</p>"; ?>

          <select class="selecao <?php echo !empty($erroTipo) ? 'error' : ''; ?>" name="tipo" required>
            <option value="">Selecione entre aluno ou professor</option>
            <option value="aluno"     <?php if(isset($tipo) && $tipo=="aluno") echo "selected"; ?>>Aluno</option>
            <option value="professor" <?php if(isset($tipo) && $tipo=="professor") echo "selected"; ?>>Professor</option>
          </select>
          <?php if (!empty($erroTipo)) echo "<p class='erro'>".htmlspecialchars($erroTipo)."</p>"; ?>

          <div class="buttons">
            <button class="btn primary" type="submit">Avançar</button>
            <button type="button" class="btn pink" onclick="location.href='../index.php'">Voltar</button>
          </div>
        </form>
      </div>

      <div class="imagens">
        <div class="mascote">
          <img src="../img/m_estrela.gif" alt="Mascote Axis">
        </div>
        <br>
        <div class="logomenor">
          <img src="../img/logomenor.png" alt="Logo Axis">
        </div>
      </div>
    </div>
  </div>
</body>
</html>
