<?php
session_start();
require __DIR__ . '/../autenticar/config.php';
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_POST['credential'])) {
    header('Location: ../index.php');
    exit;
}

$idToken = $_POST['credential'];

$client = new Google_Client(['client_id' => '496814536289-aedk1ugu37hakg4c18up25k82g236cie.apps.googleusercontent.com']);

$payload = $client->verifyIdToken($idToken);

if ($payload) {
    $googleSub  = $payload['sub'];
    $email      = $payload['email'];
    $nomeGoogle = $payload['name'] ?? '';
    $fotoGoogle = $payload['picture'] ?? '';

    $sql = "SELECT * FROM usuarios WHERE google_sub = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $googleSub, $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();

        $_SESSION['usuario'] = [
            'id'           => $user['id'],
            'email'        => $user['email'],
            'nome'         => $user['nome_completo'],
            'nome_usuario' => $user['nome_usuario']
        ];

        header("Location: ../home/home.php");
        exit;
    } else {
        $_SESSION['google_temp'] = [
            'google_sub' => $googleSub,
            'email'      => $email,
            'nome'       => $nomeGoogle,
            'foto'       => $fotoGoogle
        ];

        header("Location: cadastro-google-completar.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
