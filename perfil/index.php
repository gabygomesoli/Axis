<?php
session_start();

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../index.php");
    exit;
}

$paginaAtual = "Perfil de usuário";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <title><?= htmlspecialchars($paginaAtual) ?></title>

  <link rel="icon" type="image/png" href="../img/imgnavbar/globoaxis.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css"/>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    const PROFILE_API_BASE = 'api';
  </script>
</head>

<body data-page="Perfil">

  <nav class="navbar">
    <div class="logo-section">
      <div class="logo">
        <img src="../img/imgnavbar/globoaxis.png" alt="Axis" />
      </div>
      <a href="#" class="page-title">
        Perfil de usuário
      </a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../home/home.php">Início</a>
        <a href="../materias/materias.php">Matérias</a>
        <a href="../comunidade/public/">Comunidade</a>
        <a href="#" class="active">Perfil</a>
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
    <a href="#" class="active">Perfil</a>

    <div class="menu-divider"></div>

    <a href="../ranking/">Ranking</a>
    <a href="../dashboard/public/">Estatísticas</a>
    <a href="../redacao/public/">Redação</a>
    <a href="../corretor/">Corretor por IA</a>
    <a href="../simulado/">Simulados</a>
    <a href="../cronograma/">Cronogramas</a>

    <a href="../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay" id="menuOverlay"></div>

  <main class="page">

    <section class="column-left">
      <section class="profile-card card">
        <div class="profile-avatar-wrapper">
          <div class="avatar-ring">
            <img id="avatar-img" src="../img/perfilpadrao.png" alt="Foto de perfil" />
          </div>
          <button class="btn-outline" id="btn-change-avatar">
            <img src="../img/postar.png" alt="" style="width: 30px;"> editar foto
          </button>
          <input type="file" id="avatar-file" accept="image/*" hidden>
        </div>

        <div class="profile-main-info">
          <h2 id="profile-name">Carregando...</h2>
          <p class="profile-username">
            Usuário: <span id="profile-username">@...</span>
          </p>

          <div class="profile-badges">
            <span class="chip chip-role" id="profile-role">
              <span class="chip-icon">🎓</span> aluno
            </span>
            <span class="chip chip-points">
              <span class="chip-icon">⭐</span> <span id="profile-points">0</span> pts
            </span>
          </div>

          <button class="btn-pill" id="btn-open-edit">
            <img src="../img/editinfos.png" alt="" style="width: 20px; margin-right: 5px;">
            <span class="pill-text">editar informações</span>
          </button>

          <div class="profile-meta">
            <p><strong>E-mail:</strong> <span id="profile-email">...</span></p>
            <p><strong>Conta criada em:</strong> <span id="profile-created">--/--/----</span></p>
          </div>
        </div>
      </section>

      <section class="bottom-row">

        <section class="card small-card">
          <div class="card-header-with-icon">
            <h3>Senha:</h3>
          </div>

          <form id="form-password" class="form">
            <label>
              Senha atual
              <input type="password" id="field-current-pass" placeholder="********">
            </label>
            <label>
              Nova senha
              <input type="password" id="field-new-pass" placeholder="********">
            </label>
            <label>
              Confirmar nova senha
              <input type="password" id="field-new-pass-confirm" placeholder="********">
            </label>

            <button type="button" class="btn-primary" id="btn-change-password">
              trocar senha
            </button>
            <span class="form-msg" id="password-msg"></span>
          </form>
        </section>
      </section>
    </section>

    <section class="column-right">
      <section class="card progress-card">
        <h3>Seu progresso:</h3>
        <p class="muted">Aulas assistidas e questões respondidas.</p>

        <div class="charts-grid">
          <div class="chart-box">
            <h4>Aulas assistidas</h4>
            <canvas id="chart-lessons" height="130"></canvas>
          </div>
          <div class="chart-box">
            <h4>Questões respondidas</h4>
            <canvas id="chart-questions" height="130"></canvas>
          </div>
        </div>

        <div class="progress-footer">
          <a href="../dashboard/public/" class="btn-secondary">
            veja mais estatísticas
          </a>
        </div>
      </section>
    </section>
  </main>

  <dialog id="edit-modal" class="edit-modal">
    <form method="dialog" class="edit-form" id="form-edit-profile">
      <h3>Editar informações</h3>

      <label>
        Nome completo
        <input type="text" id="edit-name">
      </label>

      <label>
        Nome de usuário (@)
        <input type="text" id="edit-username">
      </label>

      <label>
        Tipo de usuário
        <select id="edit-type">
          <option value="aluno">Aluno</option>
          <option value="professor">Professor</option>
        </select>
      </label>

      <div class="edit-actions">
        <button value="cancel" class="btn-cancel">cancelar</button>
        <button type="submit" value="ok" class="btn-primary">salvar</button>
      </div>

      <span class="form-msg" id="edit-msg"></span>
    </form>
  </dialog>

  <script src="app.js"></script>
</body>
</html>