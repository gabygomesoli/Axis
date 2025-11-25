<?php 
session_start();
include("../../autenticar/config.php");

if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit;
}

$usuarioId = $_SESSION['usuario']['id'];

$sql = "SELECT nome_usuario FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
  header("Location: ../../autenticar/logout.php");
  exit;
}

$usuario = $res->fetch_assoc();
$nomeUsuario = $usuario['nome_usuario'];

$categoria    = 'geografia';
$subcategoria = 'geopolitica';

$paginaAtual = "Geopolítica";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= $paginaAtual ?> · AXIS</title>
  <link rel="stylesheet" href="../style_conteudos.css" />
</head>
<body>

<nav class="navbar">
  <div class="logo-section">
    <img src="../../img/imgnavbar/globoaxis.png" class="logo">
    <a class="page-title"><?= $paginaAtual ?></a>
  </div>

  <div class="nav-links">
    <div class="navopcoes">
      <a href="../../home/home.php">Início</a>
      <a href="../../materias/materias.php">Matérias</a>
      <a href="../../comunidade/public/index.php">Comunidade</a>
      <a href="../../perfil/index.php">Perfil</a>
    </div>
  </div>

  <button class="navbar-toggle"><span class="bar"></span><span class="bar"></span><span class="bar"></span></button>
</nav>

<div class="mobile-menu">
  <a href="../../home/home.php">Início</a>
  <a href="../../materias/materias.php">Matérias</a>
  <a href="../../comunidade/public/index.php">Comunidade</a>
  <a href="../../perfil/index.php">Perfil</a>
  <div class="menu-divider"></div>
  <a href="../../ranking/public/">Ranking</a>
  <a href="../../dashboard/public/">Estatísticas</a>
  <a href="../../redacao/public/">Redação</a>
  <a href="../../autenticar/logout.php">Sair</a>
</div>
<div class="menu-overlay"></div>

<div class="conteudo-biologia">
  <h2 class="titulo-aulas"><?= $paginaAtual ?></h2>

  <p class="descricao">Professores selecionados pela equipe Axis.</p>

  <div class="videos-container">
    <div class="video-card">
      <iframe src="https://www.youtube.com/embed/2uXUSLF10R0?si=yirQeleDo-5e2za9" allowfullscreen></iframe>
    </div>
    <div class="video-card">
      <iframe src="https://www.youtube.com/embed/9RnHZkhxFDA?si=5Tuj79Xh3hfRFeDE" allowfullscreen></iframe>
    </div>
  </div>

  <div class="lesson-status-group">

    <label class="status-toggle">
      <input class="axis-check" data-kind="lesson" data-key="geopolitica_aula1" type="checkbox">
      <span class="status-box"></span><span class="status-label">Aula 1 concluída</span>
    </label>

    <label class="status-toggle">
      <input class="axis-check" data-kind="lesson" data-key="geopolitica_aula2" type="checkbox">
      <span class="status-box"></span><span class="status-label">Aula 2 concluída</span>
    </label>

  </div>

  <div class="pdf-section">
    <h3>Materiais e Questões</h3>

    <div class="pdf-links">
      <a class="pdf-btn"
        href="../arquivos/apostilageopolitica.pdf"
        download="Apostila_Geopolitica.pdf">📘 Apostila — Geopolítica</a>

      <a class="pdf-btn"
        href="../arquivos/questoesgeopolitica.pdf"
        download="Exercicios_Geopolitica.pdf">📘 Exercícios — Geopolítica</a>
    </div>

    <div class="lesson-status-group2">
      <label class="status-toggle">
        <input class="axis-check" data-kind="questions" data-key="geopolitica_questoes" type="checkbox">
        <span class="status-box"></span><span class="status-label">Questões concluídas</span>
      </label>
    </div>
  </div>
</div>

<script>
const PROGRESS_API = '../backend/api/progresso_conteudos.php';
const categoria = '<?= $categoria ?>';
const subcategoria = '<?= $subcategoria ?>';

document.addEventListener('DOMContentLoaded', () => {

  const checks = document.querySelectorAll('.axis-check');

  fetch(`${PROGRESS_API}?categoria=${categoria}&subcategoria=${subcategoria}`)
    .then(r => r.json())
    .then(data => {
      const lessons = data.lessons || {};
      const questions = data.questions || {};

      checks.forEach(ch => {
        const key = ch.dataset.key;
        const kind = ch.dataset.kind;

        if (kind === 'lesson' && lessons[key]) ch.checked = true;
        if (kind === 'questions' && questions.completed) ch.checked = true;
      });
    });

  checks.forEach(ch => {
    ch.addEventListener('change', () => {

      const payload = {
        tipo: ch.dataset.kind,
        categoria,
        subcategoria,
        lesson_key: ch.dataset.kind === "lesson" ? ch.dataset.key : null,
        completed: ch.checked ? 1 : 0
      };

      fetch(PROGRESS_API,{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify(payload)
      });

    });
  });

});
</script>

</body>
</html>