<?php
session_start();
require_once __DIR__ . '/../autenticar/config.php';

if (!isset($_SESSION['usuario'])) {
  header('Location: ../index.php');
  exit;
}

$usuario     = $_SESSION['usuario'];
$userId      = (int)$usuario['id'];
$nomeUsuario = htmlspecialchars($usuario['nome_usuario'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Ranking - AXIS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="css/styles.css"/>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body data-page="Ranking">
  <nav class="navbar">
    <div class="logo-section">
      <div class="logo">
        <img src="../img/imgnavbar/globoaxis.png" alt="Axis" />
      </div>
      <a href="#" class="page-title">
        Ranking
      </a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../home/home.php">Início</a>
        <a href="../materias/materias.php">Matérias</a>
        <a href="../comunidade/public/">Comunidade</a>
        <a href="../perfil/index.php">Perfil</a>
      </div>
    </div>

    <button class="navbar-toggle" id="navbarToggle" aria-label="Abrir menu">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
  </nav>

  <div class="mobile-menu" id="mobileMenu">
    <a href="../home/home.php">Início</a>
    <a href="../materias/materias.php">Matérias</a>
    <a href="../comunidade/public/">Comunidade</a>
    <a href="../perfil/index.php">Perfil</a>

    <div class="menu-divider"></div>

    <a href="../ranking/" class="active">Ranking</a>
    <a href="../dashboard/public/">Estatísticas</a>
    <a href="../redacao/public/">Redação</a>
    <a href="../corretor/">Corretor por IA</a>
    <a href="../simulado/">Simulados</a>
    <a href="../cronograma/">Cronogramas</a>

    <a href="../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay" id="menuOverlay"></div>

  <main class="ranking-wrapper">
    <section class="intro">
      <h1>Ranking da Semana</h1>
      <p class="intro-text">
        Acompanhe seus objetivos, veja sua posição entre os outros estudantes da AXIS
        e desbloqueie insígnias conforme sua participação nas aulas, questões e comunidade.
      </p>
      <p class="week-label" id="weekLabel"></p>
    </section>

    <section class="grid">
      <article class="card card-goals">
        <div class="card-title-row">
          <h2>Objetivos</h2>
          <span class="card-subtitle">
            Objetivos selecionados pela equipe Axis para que você consiga o melhor resultado possível em seus estudos!
          </span>
        </div>
        <div class="goals-row" id="goals"></div>
      </article>

      <article class="card card-leaderboard">
        <div class="card-title-row">
          <h2>Sua posição</h2>
          <span class="card-subtitle">
            Veja sua posição em relação aos outros estudantes da Axis com base em aulas, questões e comunidade.
          </span>
        </div>
        <div id="leaderboard" class="leaderboard-list"></div>
      </article>
    </section>

    <section class="card card-badges">
      <div class="card-title-row">
        <h2>Insígnias</h2>
      </div>
      <p class="badge-hint">
        As Insígnias são alcançadas conforme sua pontuação sobe! Assista aulas, responda questões e participe da comunidade
        para desbloquear novas conquistas.
      </p>
      <div class="badge-row" id="badgeRow"></div>
    </section>

  </main>

  <script>
    window.AXIS_USER_ID = <?= $userId ?>;
  </script>
  <script src="js/app.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const navbar     = document.querySelector('.navbar');
      const toggle     = document.querySelector('.navbar-toggle');
      const mobileMenu = document.querySelector('.mobile-menu');
      const overlay    = document.querySelector('.menu-overlay');

      window.addEventListener('scroll', () => {
        if (window.scrollY > 10) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
      });

      if (toggle && mobileMenu && overlay) {
        const close = () => {
          toggle.classList.remove('active');
          mobileMenu.classList.remove('active');
          overlay.classList.remove('active');
          document.body.style.overflow = '';
        };

        toggle.addEventListener('click', () => {
          const opening = !toggle.classList.contains('active');
          toggle.classList.toggle('active');
          mobileMenu.classList.toggle('active');
          overlay.classList.toggle('active');
          document.body.style.overflow = opening ? 'hidden' : '';
        });

        overlay.addEventListener('click', close);
      }
    });
  </script>
</body>
</html>
