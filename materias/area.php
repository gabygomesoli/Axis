<?php
session_start();
include("../autenticar/config.php");

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
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
  header("Location: ../logout.php");
  exit;
}

$area    = $_GET['area']    ?? 'biologicas';
$materia = $_GET['materia'] ?? 'biologia';

$paginaAtual = basename(__FILE__);

$conteudos = [

  'biologicas' => [
    'biologia' => [
      ["titulo" => "Biotecnologia",        "icone" => "img/img_biologicas/bioenergetica.png", "link" => "conteudos/biotecnologia.php"],
      ["titulo" => "Citologia",            "icone" => "img/img_biologicas/citologia.png",     "link" => "conteudos/citologia.php"],
      ["titulo" => "Doenças",              "icone" => "img/img_biologicas/doencas.png",       "link" => "conteudos/doencas.php"],
      ["titulo" => "Ecologia",             "icone" => "img/img_biologicas/ecologia.png",      "link" => "conteudos/ecologia.php"],
      ["titulo" => "Evolução",             "icone" => "img/img_biologicas/evolucao.png",      "link" => "conteudos/evolucao.php"],
      ["titulo" => "Genética",             "icone" => "img/img_biologicas/genetica.png",      "link" => "conteudos/genetica.php"],
    ],
    'fisica' => [
      ["titulo" => "Cinemática",           "icone" => "img/img_biologicas/cinematica.png",    "link" => "conteudos/cinematica.php"],
      ["titulo" => "Dinâmica",             "icone" => "img/img_biologicas/dinamica.png",      "link" => "conteudos/dinamica.php"],
      ["titulo" => "Calorimetria",         "icone" => "img/img_biologicas/calorimetria.png",  "link" => "conteudos/calorimetria.php"],
      ["titulo" => "Eletrodinâmica",       "icone" => "img/img_biologicas/eletrodinamica.png","link" => "conteudos/eletrodinamica.php"],
      ["titulo" => "Ondulatória",          "icone" => "img/img_biologicas/ondulatoria.png",   "link" => "conteudos/ondulatoria.php"],
      ["titulo" => "Eletromagnetismo",     "icone" => "img/img_biologicas/eletromagnetismo.png","link" => "conteudos/eletromagnetismo.php"],
    ],
    'quimica' => [
      ["titulo" => "Equilíbrio Químico",   "icone" => "img/img_biologicas/equilibrio.png",    "link" => "conteudos/equilibrio-quim.php"],
      ["titulo" => "Soluções",             "icone" => "img/img_biologicas/solucoes.png",      "link" => "conteudos/quim-sol.php"],
      ["titulo" => "Química Orgânica",     "icone" => "img/img_biologicas/organica.png",      "link" => "conteudos/quim-org.php"],
      ["titulo" => "Química Ambiental",    "icone" => "img/img_biologicas/ambiental.png",     "link" => "conteudos/quim-amb.php"],
    ],
  ],

  'humanas' => [
    'filosofia' => [
      ["titulo" => "Filosofia Antiga",     "icone" => "img/img_humanas/antiga.png",           "link" => "conteudos/fil-antiga.php"],
      ["titulo" => "Filosofia Moderna",    "icone" => "img/img_humanas/moderna.png",          "link" => "conteudos/fil-moderna.php"],
      ["titulo" => "Filosofia Medieval",   "icone" => "img/img_humanas/medieval.png",         "link" => "conteudos/fil-medieval.php"],
      ["titulo" => "Filosofia Contemporânea","icone" => "img/img_humanas/contemporanea.png",  "link" => "conteudos/fil-contem.php"],
    ],
    'historia' => [
      ["titulo" => "Brasil Colônia",       "icone" => "img/img_humanas/colonia.png",          "link" => "conteudos/brasil-colo.php"],
      ["titulo" => "República",            "icone" => "img/img_humanas/republica.png",        "link" => "conteudos/republica.php"],
      ["titulo" => "Idade Moderna",        "icone" => "img/img_humanas/histmoderna.png",      "link" => "conteudos/idade-moderna.php"],
      ["titulo" => "História Geral",       "icone" => "img/img_humanas/geral.png",            "link" => "conteudos/historia-geral.php"],
    ],
    'geografia' => [
      ["titulo" => "Geopolítica",          "icone" => "img/img_humanas/geopolitica.png",      "link" => "conteudos/geo-geopolitica.php"],
      ["titulo" => "Urbanização",          "icone" => "img/img_humanas/urbanizacao.png",      "link" => "conteudos/geo-urba.php"],
      ["titulo" => "Meio Ambiente",        "icone" => "img/img_humanas/meioambiente.png",     "link" => "conteudos/geo-meio-ambiente.php"],
      ["titulo" => "Geografia Física",     "icone" => "img/img_humanas/geografiafisica.png",  "link" => "conteudos/geo-fisica.php"],
      ["titulo" => "Cartografia",          "icone" => "img/img_humanas/cartografia.png",      "link" => "conteudos/geo-carto.php"],
      ["titulo" => "Leitura de Mapas",     "icone" => "img/img_humanas/mapas.png",            "link" => "conteudos/geo-mapa.php"],
    ],
    'sociologia' => [
      ["titulo" => "Karl Marx",            "icone" => "img/img_humanas/karl.png",             "link" => "conteudos/socio-karl.php"],
      ["titulo" => "Durkheim",             "icone" => "img/img_humanas/durkheim.png",         "link" => "conteudos/socio-durkheim.php"],
      ["titulo" => "Max Weber",            "icone" => "img/img_humanas/weber.png",            "link" => "conteudos/socio-max.php"],
    ],
  ],

  'linguagens' => [
    'portugues' => [
      ["titulo" => "Redação ENEM",         "icone" => "img/img_linguagens/redacao.png",       "link" => "conteudos/linguagens-redacao.php"],
    ],
  ],

  'matematica' => [
    'geral' => [
      ["titulo" => "Porcentagem",          "icone" => "img/img_matematica/porcentagem.png",   "link" => "conteudos/mat-porcentagem.php"],
      ["titulo" => "Estatística",          "icone" => "img/img_matematica/estatistica.png",   "link" => "conteudos/mat-estatistica.php"],
      ["titulo" => "Probabilidade",        "icone" => "img/img_matematica/probabilidade.png", "link" => "conteudos/mat-probabilidade.php"],
      ["titulo" => "Geometria Plana e Espacial","icone" => "img/img_matematica/geometria.png","link" => "conteudos/mat-geo.php"],
      ["titulo" => "Regra de Três",        "icone" => "img/img_matematica/regra3.png",        "link" => "conteudos/mat-regra.php"],
      ["titulo" => "Interpretação de Gráficos","icone" => "img/img_matematica/graficos.png",  "link" => "conteudos/mat-interpretacao.php"],
    ],
  ],
];

if (!isset($conteudos[$area][$materia])) {
  $area = 'biologicas';
  $materia = 'biologia';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= ucfirst($area) ?> - <?= ucfirst($materia) ?> | Axis</title>
  <link rel="icon" type="image/png" href="../img/imgnavbar/globoaxis.png">
  <link rel="stylesheet" href="stylematerias.css">
</head>

<body class="<?= $area ?>" data-page="<?= ucfirst($materia) ?>">

  <nav class="navbar">
    <div class="logo-section">
      <img src="../img/imgnavbar/globoaxis.png" alt="Logo do site" class="logo">
      <a href="#" class="page-title">Matérias</a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../home/home.php">Início</a>
        <a href="../materias/materias.php" class="active">Matérias</a>
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

  <div class="mini-cards-container">
    <?php
    $areas = [
      "humanas"    => "Humanas",
      "biologicas" => "Biológicas",
      "matematica" => "Matemática",
      "linguagens" => "Linguagens"
    ];
    foreach ($areas as $slug => $nome):
    ?>
      <div class="mini-card <?= $slug ?> <?= ($area === $slug) ? 'ativo' : '' ?>"
        onclick="location.href='<?= $paginaAtual ?>?area=<?= $slug ?>&materia=<?= array_key_first($conteudos[$slug]) ?>'">
        <img src="img/mascote<?= $slug ?>.png" alt="<?= $nome ?>">
        <span><?= $nome ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="abas-materias">
    <?php foreach ($conteudos[$area] as $nomeMateria => $assuntos): ?>
      <button class="<?= $materia === $nomeMateria ? 'active' : '' ?>"
        onclick="location.href='<?= $paginaAtual ?>?area=<?= $area ?>&materia=<?= $nomeMateria ?>'">
        <?= ucfirst($nomeMateria) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="cards-container">
    <div class="cards-grid">
      <?php foreach ($conteudos[$area][$materia] as $assunto): ?>
        <div class="card <?= $area ?>" onclick="location.href='<?= $assunto['link'] ?>'">
          <img src="<?= $assunto['icone'] ?>" alt="<?= $assunto['titulo'] ?>">
          <h2><?= $assunto['titulo'] ?></h2>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script src="app.js"></script>
</body>

</html>