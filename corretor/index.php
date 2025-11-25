<?php
session_start();

require_once __DIR__ . '/../autenticar/config.php';
require_once __DIR__ . '/ia_gemini.php';

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit;
}

$usuarioId = $_SESSION['usuario']['id'] ?? null;

$nomeUsuario = "Usuário";
if ($usuarioId) {
  $stmtUser = $conn->prepare("SELECT nome_usuario FROM usuarios WHERE id = ?");
  $stmtUser->bind_param("i", $usuarioId);
  $stmtUser->execute();
  $resUser = $stmtUser->get_result();
  if ($resUser && $resUser->num_rows === 1) {
    $rowUser = $resUser->fetch_assoc();
    $nomeUsuario = $rowUser['nome_usuario'];
  }
  $stmtUser->close();
}

$erro = "";
$sucesso = "";
$dadosCorrecao = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo = trim($_POST['titulo'] ?? '');
  $texto  = trim($_POST['texto'] ?? '');

  if ($titulo === '' || $texto === '') {
    $erro = "Preencha o título e o texto da redação.";
  } else {
    try {
      $correcao = corrigirRedacaoComIA($texto);

      $notaGeral = $correcao['nota_geral'] ?? null;
      $competenciasJson = json_encode($correcao['competencias'] ?? []);
      $sugestoesJson    = json_encode($correcao['sugestoes'] ?? []);

      $stmt = $conn->prepare("
                INSERT INTO redacoes (user_id, titulo, texto, nota_geral_ia, competencias_ia, sugestoes_ia)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
      $stmt->bind_param(
        "ississ",
        $usuarioId,
        $titulo,
        $texto,
        $notaGeral,
        $competenciasJson,
        $sugestoesJson
      );
      $stmt->execute();
      $stmt->close();

      $sucesso = "Redação corrigida com sucesso!";
      $dadosCorrecao = $correcao;
    } catch (Exception $e) {
      $erro = "Ocorreu um erro na correção por IA: " . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Corretor de Redação por IA - AXIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <nav class="navbar">
    <div class="logo-section">
      <div class="logo">
        <img src="../img/imgnavbar/globoaxis.png" alt="Axis" />
      </div>
      <a href="#" class="page-title">
        Corretor de Redação por IA
      </a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../home/home.php">Início</a>
        <a href="../materias/materias.php">Matérias</a>
        <a href="../comunidade/public/">Comunidade</a>
        <a href="../perfil/">Perfil</a>
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

    <a href="../ranking/">Ranking</a>
    <a href="../dashboard/public/">Estatísticas</a>
    <a href="../redacao/public/">Redação</a>
    <a href="../corretor/" class="active">Corretor por IA</a>
    <a href="../simulado/">Simulados</a>
    <a href="../cronograma/">Cronogramas</a>

    <a href="../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay" id="menuOverlay"></div>

  <main class="page">
    <section class="column-left">
      <div class="card">
        <div class="card-inner">
          <div class="card-header">
            <div>
              <div class="card-title">
                Enviar redação para correção
                <span class="pill">ENEM • Competências C1–C5</span>
              </div>
              <p class="card-subtitle">
                Cole sua redação abaixo e deixe a IA da AXIS apontar forças, fraquezas e sugestões.
              </p>
            </div>
          </div>

          <?php if ($erro): ?>
            <div class="alert alert-error">
              <span class="alert-emoji">⚠️</span>
              <span><?php echo htmlspecialchars($erro); ?></span>
            </div>
          <?php endif; ?>

          <?php if ($sucesso): ?>
            <div class="alert alert-success">
              <span class="alert-emoji">✅</span>
              <span><?php echo htmlspecialchars($sucesso); ?></span>
            </div>
          <?php endif; ?>

          <form method="post" autocomplete="off">
            <div class="form-group">
              <label class="form-label">
                <span>Título da redação</span>
                <small>Ex.: Desafios na superação do racismo ambiental no Brasil</small>
              </label>
              <input
                type="text"
                name="titulo"
                class="form-input"
                placeholder="Digite um título para identificar esta redação"
                value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label class="form-label">
                <span>Texto da redação</span>
                <small>Recomendado até ~30 linhas</small>
              </label>
              <textarea
                name="texto"
                class="form-textarea"
                placeholder="Digite ou cole aqui o texto completo da sua redação, com introdução, desenvolvimento e conclusão."><?php echo htmlspecialchars($_POST['texto'] ?? ''); ?></textarea>
            </div>

            <div class="form-footer">
              <p class="form-hint">
                A correção é automática e segue um padrão aproximado do ENEM, mas não substitui o olhar do professor.
              </p>
              <button type="submit" class="btn-primary">
                <span>Corrigir com IA</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section class="column-right">
      <div class="card">
        <div class="card-inner">
          <div class="card-header">
            <div>
              <div class="card-title">
                Resultado da correção
              </div>
              <p class="card-subtitle">
                Veja a nota geral, o desempenho em cada competência e sugestões de melhoria.
              </p>
            </div>
            <span class="tag-ia">IA Gemini · gemini-2.5-flash-lite</span>
          </div>

          <?php if ($dadosCorrecao): ?>
            <?php
            $notaGeral = $dadosCorrecao['nota_geral'] ?? null;
            $competencias = $dadosCorrecao['competencias'] ?? [];
            $sugestoes = $dadosCorrecao['sugestoes'] ?? [];
            ?>
            <div>
              <?php if ($notaGeral !== null): ?>
                <p class="card-subtitle" style="margin-bottom: 0.4rem;">Nota geral estimada:</p>
                <div class="score-badge">
                  <span><?php echo htmlspecialchars($notaGeral); ?></span>
                  <small>/ 1000</small>
                </div>
              <?php else: ?>
                <p class="card-subtitle">A IA não retornou uma nota geral.</p>
              <?php endif; ?>

              <?php if (!empty($competencias)): ?>
                <div class="competencias-grid">
                  <?php foreach ($competencias as $codigo => $info): ?>
                    <div class="competencia-card">
                      <div class="competencia-header">
                        <span class="code"><?php echo htmlspecialchars($codigo); ?></span>
                        <span class="nota">
                          <?php echo htmlspecialchars($info['nota'] ?? '0'); ?>/200
                        </span>
                      </div>
                      <p class="competencia-comentario">
                        <?php echo htmlspecialchars($info['comentario'] ?? ''); ?>
                      </p>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="card-subtitle" style="margin-top: 0.6rem;">
                  As competências ainda não foram avaliadas para esta redação.
                </p>
              <?php endif; ?>

              <?php if (!empty($sugestoes)): ?>
                <div style="margin-top: 1rem;">
                  <p class="card-subtitle" style="margin-bottom: 0.2rem;">Sugestões da IA:</p>
                  <ul class="sugestoes-list">
                    <?php foreach ($sugestoes as $sug): ?>
                      <li><?php echo htmlspecialchars($sug); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <p class="card-subtitle">
              Envie uma redação pela coluna ao lado para ver a correção automática aqui.
            </p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <script>
    const navbarToggle = document.getElementById('navbarToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.getElementById('menuOverlay');

    if (navbarToggle && mobileMenu && menuOverlay) {
      navbarToggle.addEventListener('click', () => {
        navbarToggle.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        menuOverlay.classList.toggle('active');
      });

      menuOverlay.addEventListener('click', () => {
        navbarToggle.classList.remove('active');
        mobileMenu.classList.remove('active');
        menuOverlay.classList.remove('active');
      });
    }

    window.addEventListener('scroll', () => {
      const nav = document.querySelector('.navbar');
      if (!nav) return;
      if (window.scrollY > 10) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    });
  </script>
</body>

</html>