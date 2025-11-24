<?php
session_start();
include("config.php");

if (!isset($_SESSION['google_temp'])) {
    header("Location: ../index.php");
    exit;
}

$googleData = $_SESSION['google_temp'];

$erro = $erroNome = $erroTipo = $erroTermos = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_usuario = trim($_POST['nome_usuario'] ?? "");
    $tipo         = $_POST['tipo'] ?? "";
    $termos       = isset($_POST['termos']);

    if ($nome_usuario === "") {
        $erroNome = "Digite um nome de usuário.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE nome_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nome_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $erroNome = "Este nome de usuário já está em uso.";
        }
    }

    if ($tipo === "") {
        $erroTipo = "Selecione o tipo de usuário.";
    }

    if (!$termos) {
        $erroTermos = "Você deve aceitar os termos de uso e a política de privacidade.";
    }

    if (
        empty($erroNome) &&
        empty($erroTipo) &&
        empty($erroTermos)
    ) {
        $nome_completo = $googleData['nome'];
        $email         = $googleData['email'];
        $googleSub     = $googleData['google_sub'];

        $senhaAleatoria = bin2hex(random_bytes(16));
        $senhaHash      = password_hash($senhaAleatoria, PASSWORD_DEFAULT);

        $fotoPerfilPath = null;
        if (!empty($googleData['foto']) && filter_var($googleData['foto'], FILTER_VALIDATE_URL)) {
            $fotoPerfilPath = $googleData['foto'];
        }

        $cepPadrao = '00000000';

        $sql = "INSERT INTO usuarios 
                (nome_completo, nome_usuario, email, senha, tipo, cep, foto_perfil, auth_provider, google_sub)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'google', ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssss",
            $nome_completo,
            $nome_usuario,
            $email,
            $senhaHash,
            $tipo,
            $cepPadrao,
            $fotoPerfilPath,
            $googleSub
        );

        if ($stmt->execute()) {
            $_SESSION['usuario'] = [
                'id'           => $stmt->insert_id,
                'email'        => $email,
                'nome'         => $nome_completo,
                'nome_usuario' => $nome_usuario
            ];

            unset($_SESSION['google_temp']);
            header("Location: ../home/home.php");
            exit;
        } else {
            $erro = "Erro ao cadastrar: " . htmlspecialchars($stmt->error);
        }
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
  <title>Completar cadastro - Axis (Google)</title>
  <link rel="stylesheet" href="style.css?v=5">
  <link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="content">
        <form method="post" novalidate>
          <h2>Entre para a Axis!</h2>
          <h4>Confirme alguns dados para concluir o cadastro com o Google.</h4>

          <?php if (!empty($erro)): ?>
            <p class="erro erro-geral"><?php echo htmlspecialchars($erro); ?></p>
          <?php endif; ?>

          <p style="color:#cfcfcf; margin-bottom: 10px;">
            Você está usando a conta: <strong><?php echo htmlspecialchars($googleData['email']); ?></strong>
          </p>

          <select class="selecao <?php echo !empty($erroTipo) ? 'error' : ''; ?>" name="tipo" required>
            <option value="">Selecione entre aluno ou professor</option>
            <option value="aluno"     <?php if(isset($tipo) && $tipo=="aluno") echo "selected"; ?>>Aluno</option>
            <option value="professor" <?php if(isset($tipo) && $tipo=="professor") echo "selected"; ?>>Professor</option>
          </select>
          <?php if (!empty($erroTipo)) echo "<p class='erro'>".htmlspecialchars($erroTipo)."</p>"; ?>

          <input type="text" name="nome_usuario" placeholder="nome de usuário" required
                 value="<?php echo htmlspecialchars($_POST['nome_usuario'] ?? ''); ?>"
                 class="<?php echo !empty($erroNome) ? 'error' : ''; ?>">
          <?php if (!empty($erroNome)) echo "<p class='erro'>".htmlspecialchars($erroNome)."</p>"; ?>

          <label class="termos-label <?php echo !empty($erroTermos) ? 'error' : ''; ?>">
            <input type="checkbox" name="termos" <?php if(isset($termos) && $termos) echo "checked"; ?>>
            Aceito os termos de uso e a política de privacidade.
          </label>
          <?php if (!empty($erroTermos)) echo "<p class='erro'>".htmlspecialchars($erroTermos)."</p>"; ?>

          <div class="buttons">
            <button class="btn primary" type="submit">Concluir cadastro</button>
            <button type="button" class="btn pink" onclick="location.href='../index.php'">Cancelar</button>
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
