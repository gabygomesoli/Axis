<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include("../autenticar/config.php");

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit;
}

$usuarioId = $_SESSION['usuario']['id'];

$sql = "SELECT nome_usuario FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  die("Erro ao preparar a consulta: " . $conn->error);
}

$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
  $usuario = $res->fetch_assoc();
  $nomeUsuario = htmlspecialchars($usuario['nome_usuario']);
} else {
  header("Location: ../logout.php");
  exit;
}

$paginaAtual = "Matérias";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $paginaAtual; ?> - Plataforma Axis</title>
  <link rel="icon" type="image/png" href="../img/imgnavbar/globoaxis.png">
  <link rel="stylesheet" href="style.css">
</head>

<body data-page="<?php echo $paginaAtual; ?>">

  <nav class="navbar">
    <div class="logo-section">
      <img src="../img/imgnavbar/globoaxis.png" alt="Logo do site" class="logo">
      <a href="#" class="page-title"><?php echo $paginaAtual; ?></a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../home/home.php">Início</a>
        <a href="#" class="active">Matérias</a>
        <a href="../comunidade/public/">Comunidade</a>
        <a href="../perfil/public/">Perfil</a>
      </div>
    </div>

    <button class="navbar-toggle" aria-label="Abrir menu">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
  </nav>

  <div class="mobile-menu">
    <a href="../home/home.php">Início</a>
    <a href="../materias/materias.php" class="active">Matérias</a>
    <a href="../comunidade/public/">Comunidade</a>
    <a href="../perfil/public/">Perfil</a>

    <div class="menu-divider"></div>

    <a href="../ranking/public/">Ranking</a>
    <a href="../dashboard/public/">Estatísticas</a>
    <a href="../redacao/public/">Redação</a>
    <a href="../corretor/">Corretor por IA</a>
    <a href="../simulado/">Simulados</a>
    <a href="../cronograma/">Cronogramas</a>

    <a href="../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay"></div>

  <main class="materias-container">
    <button class="arrow left" aria-label="Anterior">❮</button>

    <div class="cards-carousel">
      <div class="card biologicas" onclick="window.location.href='area.php?area=biologicas&materia=biologia'">
        <img src="img/mascotebiologicas.png" alt="">
        <h2>Biológicas</h2>
        <p>Biologia, Física e Química.</p>
      </div>

      <div class="card humanas" onclick="window.location.href='area.php?area=humanas&materia=filosofia'">
        <img src="img/mascotehumanas.png" alt="">
        <h2>Humanas</h2>
        <p>Filosofia, História, Geografia e Sociologia.</p>
      </div>

      <div class="card matematica" onclick="window.location.href='area.php?area=matematica&materia=geral'">
        <img src="img/mascotematematica.png" alt="">
        <h2>Matemática</h2>
        <p>Matemática aplicada à realidade.</p>
      </div>

      <div class="card linguagens" onclick="window.location.href='area.php?area=linguagens&materia=portugues'">
        <img src="img/mascotelinguagens.png" alt="">
        <h2>Linguagens</h2>
        <p>Artes, Língua Portuguesa e Estrangeira.</p>
      </div>
    </div>

    <button class="arrow right" aria-label="Próximo">❯</button>
  </main>

  <script src="app.js"></script>
</body>

</html>