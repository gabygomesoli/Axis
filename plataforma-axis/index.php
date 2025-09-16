<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($senha, $user['senha'])) {
                $_SESSION['usuario'] = $user['email'];
                header("Location: home.php");
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
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
   <link rel="icon" type="image/png" href="img/logo.png">
  <script src="https://accounts.google.com/gsi/client" async></script>
  <script>
      function decodeJWT(token) {

        let base64Url = token.split(".")[1];
        let base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        let jsonPayload = decodeURIComponent(
          atob(base64)
            .split("")
            .map(function (c) {
              return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
        );
        return JSON.parse(jsonPayload);
      }

      function handleCredentialResponse(response) {

        console.log("Encoded JWT ID token: " + response.credential);

        const responsePayload = decodeJWT(response.credential);

        console.log("Decoded JWT ID token fields:");
        console.log("  Full Name: " + responsePayload.name);
        console.log("  Given Name: " + responsePayload.given_name);
        console.log("  Family Name: " + responsePayload.family_name);
        console.log("  Unique ID: " + responsePayload.sub);
        console.log("  Profile image URL: " + responsePayload.picture);
        console.log("  Email: " + responsePayload.email);
      }
      $ JWT="eyJhbGciOiJSUzI1Ni ... Hecz6Wm4Q"
      $ echo $JWT | jq -R 'split(".") | .[0],.[1] | @base64d | fromjson'
{
  "alg": "RS256",
  "kid": "c7e04465649ffa606557650c7e65f0a87ae00fe8",
  "typ": "JWT"
}
{
  "iss": "https://accounts.google.com",
  "azp": "123456789012-s0meRand0mStR1nG.apps.googleusercontent.com",
  "aud": "123456789012-s0meRand0mStR1nG.apps.googleusercontent.com",
  "sub": "2718281828459045",
  "email": "example@example.com",
  "email_verified": true,
  "nbf": 1744645148,
  "name": "Brian Daugherty",
  "picture": "https://lh3.googleusercontent.com/a/08a898b88ca4d6407be652d8",
  "given_name": "Brian",
  "family_name": "Daugherty",
  "iat": 1744645448,
  "exp": 1744649048,
  "jti": "52cd32984b30e178aa88bc2e75e63e055a461fcf"
}
    </script>
    
</head>
<body>
<div class="container">
  <form method="post" class="card">
    <h2>Seja bem-vindo!</h2>
    <h4> Faça login para acessar a sua conta ou cadastre-se. </h4>
    <?php if(isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Entrar</button>
    <div class="separator"><span> ou use para entrar: </span></div>
    <!-- g_id_onload contains Google Identity Services settings -->
    <div
      id="g_id_onload"
      data-auto_prompt="false"
      data-callback="handleCredentialResponse"
      data-client_id="367510779734-1e61dpb6cmt2hkbrh8jusssl07r920oj.apps.googleusercontent.com"
    ></div>
    <!-- g_id_signin places the button on a page and supports customization -->
    <div class="g_id_signin" data-theme="filled_blue" data-text="signup_with"></div>
    <button type="button" onclick="window.location.href='cadastro.php'">Cadastre-se</button>
  </form>
</div>
</body>
</html>
