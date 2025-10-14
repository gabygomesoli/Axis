<?php
require_once "db.php";


$erro = "";

if (!isset($_SESSION['cadastro'])) {
    header("Location: cadastro1.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['nome_usuario']);
    $cep = trim($_POST['cep']);

    if (strlen($cep) != 8 || !ctype_digit($cep)) {
        $erro = "O CEP deve conter 8 números.";
    } elseif (!isset($_POST['termos'])) {
        $erro = "Você precisa aceitar os termos.";
    } else {
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtCheck->execute([$username]);

        if ($stmtCheck->fetch()) {
            $erro = "Nome de usuário já existe.";
        } else {
            $pasta = "uploads/";
            if (!is_dir($pasta)) mkdir($pasta, 0777, true);

            $foto = $_FILES['foto_perfil'];
            $caminhoFoto = null;
            if ($foto && $foto['tmp_name']) {
                $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
                $novoNome = uniqid() . "." . strtolower($extensao);
                $caminhoFoto = $pasta . $novoNome;
                move_uploaded_file($foto['tmp_name'], $caminhoFoto);
            }

            $dados = $_SESSION['cadastro'];

            $sql = "INSERT INTO users (email, password_hash, nome_completo, name, username, tipo, cep, foto_perfil, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        $dados['email'],
        $dados['senha'],
        $dados['nome_completo'],
        $dados['nome_completo'], // <-- aqui usamos nome_completo também para o campo name
        $username,
        $dados['tipo'],
        $cep,
        $caminhoFoto
    ]);

            if ($ok) {
                $_SESSION['usuario_id'] = $pdo->lastInsertId();
                $_SESSION['usuario'] = $username;
                $_SESSION['foto'] = $caminhoFoto;
                unset($_SESSION['cadastro']);
                header("Location: home.php");
                exit;
            } else {
                $erro = "Erro ao salvar no banco.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro - Etapa 2</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<form action="" method="POST" enctype="multipart/form-data" class="card">
    <h2>Entre para a Axis!</h2>
    <?php if($erro) echo "<p class='erro'>$erro</p>"; ?>
    <input type="text" name="cep" placeholder="CEP" required maxlength="8">
    <h2>Perfil de Usuário:</h2>
    <input type="file" name="foto_perfil" accept="image/*" required>
    <input type="text" name="nome_usuario" placeholder="Nome de usuário" required>
    <label>
        <input type="checkbox" name="termos"> Aceito os termos e condições
    </label>
    <button type="submit">Confirmar Cadastro</button>
</form>
</div>
</body>
</html>
