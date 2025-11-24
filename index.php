<?php
session_start();
include("autenticar/config.php");

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = $_POST['email'] ?? "";
    $senha = $_POST['senha'] ?? "";

    if ($email !== "" && $senha !== "") {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user['auth_provider'] !== 'local') {
                $erro = "Esta conta foi criada com login do Google. Use o botão 'Entrar com Google'.";
            } elseif (password_verify($senha, $user['senha'])) {
                $_SESSION['usuario'] = [
                    'id'           => $user['id'],
                    'email'        => $user['email'],
                    'nome'         => $user['nome_completo'],
                    'nome_usuario' => $user['nome_usuario']
                ];
                header("Location: home/home.php");
                exit;
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Axis</title>
    <link rel="stylesheet" href="autenticar/style.css?v=5">
    <link rel="icon" href="img/logo.png" type="image/png">

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'autenticar/google_login.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'credential';
            input.value = response.credential;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>

<body>
    <div class="card">
        <div class="content">
            <form method="post" novalidate>
                <h2>Seja bem-vindo!</h2>
                <h4>Faça login para acessar a sua conta ou cadastre-se.</h4>

                <?php if (!empty($erro)): ?>
                    <p class="erro erro-geral"><?php echo htmlspecialchars($erro); ?></p>
                <?php endif; ?>

                <div class="field">
                    <input type="email" name="email" placeholder="e-mail" required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="field">
                    <input type="password" name="senha" placeholder="senha" required>
                </div>

                <div class="buttons">
                    <button class="btn primary" type="submit">Entrar</button>
                </div>

                <div class="separator"><span>Ou use para entrar:</span></div>

                <div class="google-wrap">
                    <div id="g_id_onload"
                        data-client_id="496814536289-aedk1ugu37hakg4c18up25k82g236cie.apps.googleusercontent.com"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin"
                        data-type="standard"
                        data-theme="outline"
                        data-size="large"
                        data-shape="pill"
                        data-text="continue_with"
                        data-logo_alignment="left">
                    </div>
                </div>

                <p class="google-info">
                    Ao continuar com o Google, você poderá entrar ou criar sua conta Axis.
                </p>

                <div class="buttons" style="margin-top: 12px;">
                    <button type="button" class="btn pink" onclick="location.href='autenticar/cadastro.php'">
                        Cadastre-se com e-mail
                    </button>
                </div>
            </form>
        </div>

        <div class="imagens">
            <div class="mascote"><img src="img/mascote.gif" alt="Mascote Axis"></div>
            <br>
            <div class="logomenor"><img src="img/logomenor.png" alt="Logo Axis"></div>
        </div>
    </div>
</body>

</html>