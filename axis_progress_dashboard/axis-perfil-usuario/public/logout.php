<?php
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Até breve · AXIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/styles.css"/>
  <style>
    .bye-wrap{min-height:70vh;display:flex;align-items:center;justify-content:center}
    .bye-card{background:var(--card);border:1px solid var(--stroke);border-radius:18px;padding:28px;text-align:center;max-width:560px;box-shadow:var(--shadow)}
    .bye-card h1{margin:0 0 8px}
    .bye-card p{color:var(--muted)}
    .bye-card a{display:inline-block;margin-top:16px}
  </style>
</head>
<body>
  <nav class="topbar">
    <div class="logo"><span class="globe">🌐</span><span class="title">AXIS</span></div>
  </nav>

  <div class="bye-wrap">
    <div class="bye-card">
      <h1>Obrigado por utilizar a plataforma! 👋</h1>
      <p>Sua sessão foi encerrada com segurança. Esperamos te ver novamente em breve.</p>
      <a class="chip" href="index.php">voltar para o início</a>
    </div>
  </div>
</body>
</html>
