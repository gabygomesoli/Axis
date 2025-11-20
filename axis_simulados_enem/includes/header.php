<?php
// header.php - layout principal do AXIS (versão simplificada)
// Ajuste os links e logo conforme o restante do seu sistema.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>AXIS – Simulados ENEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="/public/css/style.css" />
</head>
<body>
<div class="app-shell">
  <header class="axis-header">
    <div class="brand">
      <a href="/index.php" class="brand-logo">AX</a>
      <div class="brand-title">AXIS Educação</div>
    </div>

    <nav>
      <div class="nav-links">
        <a href="/index.php" class="nav-link">Início</a>
        <a href="/materias.php" class="nav-link">Matérias</a>
        <a href="/simulados_enem.php" class="nav-link active">Simulados</a>
        <a href="/comunidade.php" class="nav-link">Comunidade</a>
        <a href="/perfil.php" class="nav-link">Perfil</a>
      </div>
      <button class="nav-burger" aria-label="Menu">
        <span></span>
      </button>
    </nav>
  </header>
