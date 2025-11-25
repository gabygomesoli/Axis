<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title>Simulados</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
  <nav class="navbar">
    <div class="logo-section">
      <div class="logo">
        <img src="../img/imgnavbar/globoaxis.png" alt="Axis" />
      </div>
      <a href="#" class="page-title">
        Simulados
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

    <button class="navbar-toggle" id="navbarToggle" aria-label="Abrir menu" aria-expanded="false">
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

    <a href="../ranking/">Ranking</a>
    <a href="../dashboard/public/">Estatísticas</a>
    <a href="../redacao/public/">Redação</a>
    <a href="../corretor/">Corretor por IA</a>
    <a href="../simulado/" class="active">Simulados</a>
    <a href="../cronograma/">Cronogramas</a>

    <a href="../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay" id="menuOverlay"></div>
  
  <?php
  $years = [2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011, 2010, 2009];
  $side = 'right';
  ?>

  <section class="simulados-wrapper" aria-labelledby="sim-title">
    <h2 id="sim-title" class="sim-title">Baixe todas as provas e gabaritos dos últimos anos:</h2>

    <div class="sim-timeline" role="list">
      <div class="timeline-line" aria-hidden="true"></div>

      <?php foreach ($years as $year): ?>
        <div class="sim-item <?= $side ?>" role="listitem" aria-label="Ano <?= htmlspecialchars($year) ?>">
          <div class="marker" aria-hidden="true"></div>

          <?php if ($year == 2025): ?>
            <img src="assets/stickers/2025.gif" class="sticker" alt="Decoração" style="width:130px;height:130px;">
          <?php endif; ?>

          <?php if ($year == 2022): ?>
            <img src="assets/stickers/2022.gif" class="sticker" alt="Decoração" style="width:220px;height:220px;">
          <?php endif; ?>

          <?php if ($year == 2019): ?>
            <img src="assets/stickers/2019.gif" class="sticker" alt="Decoração" style="width:130px;height:130px;">
          <?php endif; ?>

          <?php if ($year == 2016): ?>
            <img src="assets/stickers/2016.gif" class="sticker" alt="Decoração" style="width:130px;height:130px;">
          <?php endif; ?>

          <?php if ($year == 2013): ?>
            <img src="assets/stickers/2013.gif" class="sticker" alt="Decoração" style="width:130px;height:130px;">
          <?php endif; ?>

          <?php if ($year == 2010): ?>
            <img src="assets/stickers/2010.gif" class="sticker" alt="Decoração" style="width:130px;height:130px;">
          <?php endif; ?>

          <div class="sim-card" role="group" aria-label="Arquivos <?= htmlspecialchars($year) ?>">
            <div class="col left">
              <a class="link-btn" href="provas/prova_1 (<?= $year ?>).pdf" target="_blank" rel="noopener noreferrer">
                <span>1ª Prova</span>
              </a>
              <a class="link-btn" href="provas/prova_2 (<?= $year ?>).pdf" target="_blank" rel="noopener noreferrer">
                <span>2ª Prova</span>
              </a>
            </div>

            <div class="divider" aria-hidden="true"></div>

            <div class="col right">
              <a class="link-btn" href="provas/gabarito_1 (<?= $year ?>).pdf" target="_blank" rel="noopener noreferrer">
                <span>1º Gabarito</span>
              </a>
              <a class="link-btn" href="provas/gabarito_2 (<?= $year ?>).pdf" target="_blank" rel="noopener noreferrer">
                <span>2º Gabarito</span>
              </a>
            </div>
          </div>

          <div class="sim-year"><?= htmlspecialchars($year) ?></div>
        </div>

        <?php $side = ($side === 'right') ? 'left' : 'right'; ?>
      <?php endforeach; ?>

    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const navbarToggle = document.getElementById('navbarToggle');
      const mobileMenu   = document.getElementById('mobileMenu');
      const menuOverlay  = document.getElementById('menuOverlay');
      const navbar       = document.querySelector('.navbar');

      if (!navbarToggle || !mobileMenu || !menuOverlay) return;

      const openMenu = () => {
        navbarToggle.classList.add('active');
        mobileMenu.classList.add('active');
        menuOverlay.classList.add('active');
        navbarToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
      };

      const closeMenu = () => {
        navbarToggle.classList.remove('active');
        mobileMenu.classList.remove('active');
        menuOverlay.classList.remove('active');
        navbarToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      };

      navbarToggle.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.contains('active');
        if (isOpen) closeMenu();
        else openMenu();
      });

      menuOverlay.addEventListener('click', closeMenu);

      mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeMenu();
        }
      });

      window.addEventListener('scroll', () => {
        if (!navbar) return;
        if (window.scrollY > 10) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
      });
    });
  </script>
</body>
</html>
