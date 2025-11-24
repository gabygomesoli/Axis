<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}

$paginaAtual = "Redação";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $paginaAtual; ?> - Axis</title>
  <link rel="icon" type="image/png" href="../../img/imgnavbar/globoaxis.png" />
  <link rel="stylesheet" href="style.css" />
</head>

<body data-page="<?php echo $paginaAtual; ?>">

  <nav class="navbar">
    <div class="logo-section">
      <img src="../../img/imgnavbar/globoaxis.png" alt="Logo do site" class="logo">
      <a href="#" class="page-title"><?php echo $paginaAtual; ?></a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../../home/home.php">Início</a>
        <a href="../../materias/materias.php">Matérias</a>
        <a href="../../comunidade/public/">Comunidade</a>
        <a href="../../perfil/index.php">Perfil</a>
      </div>
    </div>

    <button class="navbar-toggle" aria-label="Abrir menu">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
  </nav>

  <div class="mobile-menu">
    <a href="../../home/home.php">Início</a>
    <a href="../../materias/materias.php">Matérias</a>
    <a href="../../comunidade/public/">Comunidade</a>
    <a href="../../perfil/index.php">Perfil</a>

    <div class="menu-divider"></div>

    <a href="../../ranking/" >Ranking</a>
    <a href="../../dashboard/public/">Estatísticas</a>
    <a href="../../redacao/public/" class="active">Redação</a>
    <a href="../../corretor/">Corretor por IA</a>
    <a href="../../simulado/">Simulados</a>
    <a href="../../cronograma/">Cronogramas</a>

    <a href="../../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay"></div>

  <div class="comunidade-container">
    <aside class="sidebar">
      <div class="acoesforum">
        <div class="menu-lateral">
          <button id="btn-scroll-compose">
            <img src="../../img/postar.png" alt="Postar" id="iconlateral">Escrever redação
          </button>
          <button id="btn-profile">
            <img src="../../img/perfil.png" alt="Perfil" id="iconlateral">Meu Perfil
          </button>
        </div>
      </div>

      <div class="hashtags">
        <h3>Hashtags de redação:</h3>
        <div class="linha"></div>
        <a href="#">#enem</a>
        <div class="pular"></div>
        <a href="#">#competencias</a>
        <div class="pular"></div>
        <a href="#">#tema-da-semana</a>
        <div class="pular"></div>
        <a href="#">#folhadealmaço</a>
      </div>
    </aside>

    <section class="feed">

      <div class="card" id="compose-card">
        <div class="timeline-header">
          <h3 style="color: #fff;">Nova redação</h3>
          <p class="muted">
            Escreva sua redação na folha de almaço abaixo. Limite aproximado: <strong>66 colunas × 30 linhas</strong>.
          </p>
        </div>
        <div class="compose-body">
          <input id="red-title" class="title-input" placeholder="Título da redação" />
          <textarea id="red-text" class="almaco edit" spellcheck="false"
            placeholder="Escreva aqui sua redação..."></textarea>

          <div class="compose-footer">
            <div class="muted">
              Caracteres: <span id="count-ch">0</span> • Linhas: <span id="count-ln">0</span>
            </div>
            <div class="compose-actions">
              <input id="search" placeholder="Buscar (ex.: from:@usuario tema)" />
              <button id="btn-search" class="btn-secondary">Buscar</button>
              <button id="btn-publish" class="btn-primary">Publicar redação</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top: 20px;">
        <div class="timeline-header">
          <div class="timeline-header-row">
            <h3 style="color: #fff;">Redações publicadas</h3>
            <div class="pagination">
              <button id="prev" class="btn-secondary">« Anterior</button>
              <span class="muted" id="page-info"></span>
              <button id="next" class="btn-primary">Próxima »</button>
            </div>
          </div>
        </div>
        <div id="redacoes"></div>
      </div>

    </section>
  </div>

  <script src="app.js"></script>
</body>

</html>
