<?php
session_start();
include("config.php");

if (!isset($_SESSION['cadastro_temp'])) {
    header("Location: cadastro.php");
    exit;
}

$erro = $erroNome = $erroTermos = $erroFoto = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_usuario = trim($_POST['nome_usuario'] ?? "");
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

    if (!$termos) {
        $erroTermos = "Você deve aceitar os termos de uso e a política de privacidade.";
    }

    $fotoPerfilPath = null;
    $fotoTemp       = null;

    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] === UPLOAD_ERR_NO_FILE) {
        $erroFoto = "Envie uma foto de perfil.";
    } else {
        $fileError = $_FILES['foto_perfil']['error'];
        $fileSize  = $_FILES['foto_perfil']['size'];
        $fileName  = $_FILES['foto_perfil']['name'];
        $fileTmp   = $_FILES['foto_perfil']['tmp_name'];

        if ($fileError !== UPLOAD_ERR_OK) {
            $erroFoto = "Erro ao enviar arquivo. Tente novamente.";
        } else {
            if ($fileSize > 2 * 1024 * 1024) { // 2MB
                $erroFoto = "A foto deve ter no máximo 2MB.";
            } else {
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $extPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $extPermitidas)) {
                    $erroFoto = "Formato de imagem inválido. Envie JPG, JPEG, PNG ou WEBP.";
                } else {
                    $novoNome       = uniqid("perfil_", true) . "." . $ext;
                    $dirRel         = "uploads/perfis/";
                    $fotoPerfilPath = $dirRel . $novoNome;
                    $fotoTemp       = $fileTmp;
                }
            }
        }
    }

    if (
        empty($erroNome) &&
        empty($erroTermos) &&
        empty($erroFoto)
    ) {
        $dadosEtapa1   = $_SESSION['cadastro_temp'];
        $nome_completo = $dadosEtapa1['nome_completo'];
        $email         = $dadosEtapa1['email'];
        $senhaHash     = $dadosEtapa1['senha_hash'];
        $tipo          = $dadosEtapa1['tipo'];

        $cepPadrao = '00000000';

        $basePath     = realpath(__DIR__ . "/..");
        $uploadDirAbs = $basePath . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "perfis";

        if (!is_dir($uploadDirAbs)) {
            mkdir($uploadDirAbs, 0775, true);
        }

        $destinoAbs = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fotoPerfilPath);

        if (!move_uploaded_file($fotoTemp, $destinoAbs)) {
            $erro = "Erro ao salvar a foto de perfil. Tente novamente.";
        } else {
            $sql = "INSERT INTO usuarios (nome_completo, nome_usuario, email, senha, tipo, cep, foto_perfil, auth_provider)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'local')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssss",
                $nome_completo,
                $nome_usuario,
                $email,
                $senhaHash,
                $tipo,
                $cepPadrao,
                $fotoPerfilPath
            );

            if ($stmt->execute()) {
                unset($_SESSION['cadastro_temp']);
                echo "<script>alert('Cadastro realizado com sucesso!');location.href='../index.php';</script>";
                exit;
            } else {
                $erro = "Erro ao cadastrar: " . htmlspecialchars($stmt->error);
            }
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
  <title>Cadastro - Axis (Etapa 2)</title>
  <link rel="stylesheet" href="style.css?v=5">
  <link rel="icon" href="../img/logo.png" type="image/png">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="content">
        <form method="post" enctype="multipart/form-data" novalidate>
          <h2>Entre para a Axis!</h2>
          <h4>Continue o cadastro para iniciar seus estudos!</h4>

          <?php if (!empty($erro)): ?>
            <p class="erro erro-geral"><?php echo htmlspecialchars($erro); ?></p>
          <?php endif; ?>

          <div class="separator">Perfil de usuário</div>

          <input 
            type="file" 
            name="foto_perfil" 
            id="foto_perfil" 
            accept="image/*"
            class="<?php echo !empty($erroFoto) ? 'error' : ''; ?>">
          <?php if (!empty($erroFoto)) echo "<p class='erro'>".htmlspecialchars($erroFoto)."</p>"; ?>

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
            <button class="btn primary" type="submit">Confirmar cadastro</button>
            <button type="button" class="btn pink" onclick="location.href='cadastro.php'">Voltar</button>
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
