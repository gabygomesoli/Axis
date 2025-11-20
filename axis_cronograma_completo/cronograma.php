<?php
// ---------------------------------------------------------------------
// CONEXÃO COM O BANCO (AJUSTE OS DADOS CONFORME SEU AMBIENTE)
// ---------------------------------------------------------------------
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'axis_cronograma';

$mysqli = new mysqli($host, $usuario, $senha, $banco);
if ($mysqli->connect_errno) {
    die('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// ---------------------------------------------------------------------
// LER LISTA DE SEMANAS DISPONÍVEIS
// ---------------------------------------------------------------------
$semanasDisponiveis = [];
$sqlSemanas = "SELECT DISTINCT semana FROM cronograma_estudos ORDER BY semana";
if ($resultadoSemanas = $mysqli->query($sqlSemanas)) {
    while ($row = $resultadoSemanas->fetch_assoc()) {
        $semanasDisponiveis[] = (int)$row['semana'];
    }
    $resultadoSemanas->free();
}

if (empty($semanasDisponiveis)) {
    // fallback se o banco estiver vazio
    $semanasDisponiveis = [1];
}

// ---------------------------------------------------------------------
// DEFINIR SEMANA ATUAL (GET ?semana=) COM VALIDAÇÃO
// ---------------------------------------------------------------------
$semanaAtual = isset($_GET['semana']) ? (int) $_GET['semana'] : $semanasDisponiveis[0];
if (!in_array($semanaAtual, $semanasDisponiveis, true)) {
    $semanaAtual = $semanasDisponiveis[0];
}

// ---------------------------------------------------------------------
// BUSCAR CRONOGRAMA DA SEMANA ATUAL
// ---------------------------------------------------------------------
$diasSemana = []; // [dia][ ] = atividades

$sqlCronograma = "SELECT dia, numero, titulo 
                  FROM cronograma_estudos 
                  WHERE semana = ?
                  ORDER BY dia, numero";
$stmt = $mysqli->prepare($sqlCronograma);
$stmt->bind_param('i', $semanaAtual);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $dia = (int)$row['dia'];
    if (!isset($diasSemana[$dia])) {
        $diasSemana[$dia] = [];
    }
    $diasSemana[$dia][] = [
        'numero' => (int)$row['numero'],
        'titulo' => $row['titulo']
    ];
}
$stmt->close();

// Se por algum motivo não houver registros para a semana, evita erro
if (empty($diasSemana)) {
    $diasSemana = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>AXIS • Cronograma</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="app">
    <header class="axis-header">
        <div class="axis-logo">
            <div class="axis-logo-icon">🌐</div>
            <div class="axis-logo-text">AXIS</div>
        </div>

        <nav class="axis-nav">
            <a href="#" class="nav-pill nav-pill-active">Cronograma</a>
            <a href="#" class="nav-pill">Início</a>
            <a href="#" class="nav-pill">Matérias</a>
            <a href="#" class="nav-pill">Comunidade</a>
            <a href="#" class="nav-pill">Perfil</a>
        </nav>

        <button class="axis-menu-btn">
            <span></span><span></span><span></span>
        </button>
    </header>

    <main class="axis-main">
        <section class="study-plan-card">

            <!-- Cabeçalho do plano -->
            <header class="study-plan-header">
                <div>
                    <div class="study-plan-title">
                        <h2>Plano de Estudos</h2>
                        <p><?php echo $semanaAtual; ?>ª Semana</p>
                    </div>

                    <!-- FILTRO DE SEMANAS (DINÂMICO, VINDO DO BANCO) -->
                    <div class="week-filter">
                        <?php foreach ($semanasDisponiveis as $numeroSemana): ?>
                            <a href="?semana=<?php echo $numeroSemana; ?>"
                               class="week-pill <?php echo $numeroSemana === $semanaAtual ? 'week-pill-active' : ''; ?>">
                                <?php echo $numeroSemana; ?>ª Semana
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="study-plan-progress">
                    <div class="progress-circle">
                        <span>0%</span>
                    </div>
                </div>
            </header>

            <!-- TIMELINE DA SEMANA SELECIONADA -->
            <div class="timeline">
                <?php if (!empty($diasSemana)): ?>
                    <?php ksort($diasSemana); ?>
                    <?php foreach ($diasSemana as $dia => $atividades): ?>
                        <div class="timeline-day">
                            <div class="timeline-day-label">
                                <?php echo $dia; ?>º Dia
                            </div>

                            <div class="timeline-row">
                                <?php foreach ($atividades as $atividade): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-icon">
                                            <span>📘</span>
                                        </div>
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-content">
                                            <span class="timeline-step">
                                                <?php echo $atividade['numero']; ?>
                                            </span>
                                            <p><?php echo $atividade['titulo']; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="timeline-footer">
                        <div class="timeline-star">⭐</div>
                        <span>Você concluiu o plano da <?php echo $semanaAtual; ?>ª semana!</span>
                    </div>
                <?php else: ?>
                    <p style="margin-top: 16px; font-size: 14px; color: #D4E4FF;">
                        Nenhum conteúdo cadastrado para esta semana ainda.
                    </p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
