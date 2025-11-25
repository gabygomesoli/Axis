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

if ($res->num_rows === 1) {
  $usuario = $res->fetch_assoc();
  $nomeUsuario = $usuario['nome_usuario'];
} else {
  header("Location: ../../autenticar/logout.php");
  exit;
}

$categoria    = 'matematica';
$subcategoria = 'geometria plana e espacial';

$stmt = $conn->prepare("SELECT * FROM pdfs WHERE categoria = ? AND subcategoria = ?");
$stmt->bind_param("ss", $categoria, $subcategoria);
$stmt->execute();
$materiais = $stmt->get_result();

$paginaAtual = "Geometria Plana e Espacial";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($paginaAtual) ?> · AXIS</title>
  <link rel="stylesheet" href="../style_conteudos.css" />
</head>

<body>
  <nav class="navbar">
    <div class="logo-section">
      <img src="../../img/imgnavbar/globoaxis.png" alt="Logo do site" class="logo">
      <a href="#" class="page-title"><?= htmlspecialchars($paginaAtual) ?></a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../../home/home.php">Início</a>
        <a href="../../materias/materias.php">Matérias</a>
        <a href="../../comunidade/public/index.php">Comunidade</a>
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
    <h2 class="titulo-aulas"><?= htmlspecialchars($paginaAtual) ?></h2>
    <p class="descricao">Professores selecionados pela equipe Axis, que disponibilizam conteúdo gratuito no YouTube.</p>

    <div class="videos-container">
      <div class="video-card">
        <iframe src="https://www.youtube.com/embed/EzGf1UEnnsY?si=bzZ6zN1OvJAFCq7a" allowfullscreen></iframe>
      </div>
      <div class="video-card">
        <iframe src="https://www.youtube.com/embed/Y_gD7S6OkC4?si=dHeio1b6PEgzLcj-" allowfullscreen></iframe>
      </div>
    </div>

    <div class="lesson-status-group">
      <label class="status-toggle">
        <input
          type="checkbox"
          class="axis-check"
          data-kind="lesson"
          data-key="geometria_aula1"
          name="aula1_concluida">
        <span class="status-box"></span>
        <span class="status-label">Aula 1 concluída</span>
      </label>

      <label class="status-toggle">
        <input
          type="checkbox"
          class="axis-check"
          data-kind="lesson"
          data-key="geometria_aula2"
          name="aula2_concluida">
        <span class="status-box"></span>
        <span class="status-label">Aula 2 concluída</span>
      </label>
    </div>

    <div class="pdf-section">
      <h3>Materiais e Questões</h3>

      <div class="pdf-links">
        <a href="../arquivos/apostilap_e_gp_ga_g.pdf"
           class="pdf-btn"
           download="Apostila_Geometria.pdf">
          📘 Apostila — Geometria Plana e Espacial
        </a>

        <a href="../arquivos/questoesgeometria_espacial.pdf"
           class="pdf-btn"
           download="Exercicios_Geometria_Espacial.pdf">
          📘 Exercícios — Geometria Espacial
        </a>

        <a href="../arquivos/questoesgeometriaplana.pdf"
           class="pdf-btn"
           download="Exercicios_Geometria_Plana.pdf">
          📘 Exercícios — Geometria Plana
        </a>
      </div>

      <div class="lesson-status-group2">
        <label class="status-toggle">
          <input
            type="checkbox"
            class="axis-check"
            data-kind="questions"
            data-key="geometria_questoes"
            name="questoes_concluidas">
          <span class="status-box"></span>
          <span class="status-label">Questões concluídas</span>
        </label>
      </div>
    </div>
  </div>

  <script>
    const PROGRESS_API = '../backend/api/progresso_conteudos.php';
    const categoria    = '<?= addslashes($categoria) ?>';
    const subcategoria = '<?= addslashes($subcategoria) ?>';

    document.addEventListener('DOMContentLoaded', () => {
      const checks = document.querySelectorAll('.axis-check');
      if (!checks.length) return;

      fetch(
        PROGRESS_API +
        '?categoria='   + encodeURIComponent(categoria) +
        '&subcategoria='+ encodeURIComponent(subcategoria)
      )
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          const lessons   = data.lessons   || {};
          const questions = data.questions || { completed: false };

          checks.forEach(ch => {
            const kind = ch.dataset.kind;
            const key  = ch.dataset.key;

            if (kind === 'lesson' && lessons[key]) {
              ch.checked = true;
            }
            if (kind === 'questions' && questions.completed) {
              ch.checked = true;
            }
          });
        })
        .catch(() => console.warn('Não foi possível carregar o progresso agora.'));

      checks.forEach(ch => {
        ch.addEventListener('change', () => {
          const payload = {
            tipo:        ch.dataset.kind,
            categoria:   categoria,
            subcategoria: subcategoria,
            lesson_key:  ch.dataset.kind === 'lesson' ? ch.dataset.key : null,
            completed:   ch.checked ? 1 : 0
          };

          fetch(PROGRESS_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          })
          .then(r => r.json())
          .then(resp => {
            if (!resp.ok) {
              alert(resp.message || 'Erro ao salvar progresso.');
            }
          })
          .catch(() => {
            alert('Erro de rede ao salvar progresso.');
          });
        });
      });
    });
  </script>
</body>
</html>