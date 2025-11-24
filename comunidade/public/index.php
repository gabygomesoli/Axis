<?php
session_start();

if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit;
}

$paginaAtual = "Comunidade";
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
        <a href="./" class="active">Comunidade</a>
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
    <a href="./" class="active">Comunidade</a>
    <a href="../../perfil/index.php">Perfil</a>

    <div class="menu-divider"></div>

    <a href="../../ranking/">Ranking</a>
    <a href="../../dashboard/public/">Estatísticas</a>
    <a href="../../redacao/public/">Redação</a>
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
          <button id="btn-post">
            <img src="../../img/postar.png" alt="Postar" id="iconlateral">Fazer Post
          </button>
          <button id="btn-notifications">
            <img src="../../img/notificacao.png" alt="Notificações" id="iconlateral">Notificações
          </button>
          <button id="btn-profile">
            <img src="../../img/perfil.png" alt="Perfil" id="iconlateral">Meu Perfil
          </button>
        </div>
      </div>

      <div class="hashtags">
        <h3>Hashtags Relevantes:</h3>
        <div class="linha"></div>
        <a href="#">#matematica</a>
        <div class="pular"></div>
        <a href="#">#temadaredacao</a>
        <div class="pular"></div>
        <a href="#">#retafinal</a>
        <div class="pular"></div>
        <a href="#">#estudenaAXIS</a>
        <div class="pular"></div>
        <a href="#">#cadernorosa</a>
        <div class="pular"></div>
        <a href="#">#surtando</a>
        <div class="pular"></div>
        <a href="#">#tachegando</a>
        <div class="pular"></div>
        <a href="#">#studytok</a>
        <div class="espaco"></div>
      </div>
    </aside>

    <section class="feed">
      <div id="timeline" class="card">
        <div class="timeline-header">
          <div class="search-box">
            <input type="text" id="search" placeholder="Buscar... (ex: from:@usuario)" />
            <button id="search-btn">Buscar</button>
          </div>
        </div>
        <div id="posts"></div>
      </div>
    </section>
  </div>

  <div id="post-modal" class="modal">
    <div class="modal-content">
      <span id="close-post" class="close-btn">&times;</span>
      <h3>Nova Postagem</h3>
      <textarea id="post-content" placeholder="O que você quer compartilhar?" rows="4"></textarea>
      <div class="actions">
        <button id="btnpublicar">Publicar</button>
      </div>
    </div>
  </div>

  <div id="notif-modal"></div>

  <script src="app.js"></script>

</body>

</html>