<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

// Buscar arquivos de simulados do banco
$sql = "SELECT id, ano, categoria, label, url, ordem
        FROM enem_arquivos
        ORDER BY ano DESC, categoria ASC, ordem ASC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();

// Agrupa por ano e categoria
$simulados = [];
foreach ($rows as $row) {
    $ano = (int) $row['ano'];
    if (!isset($simulados[$ano])) {
        $simulados[$ano] = [
            'ano' => $ano,
            'provas' => [],
            'gabaritos' => [],
            'mascote' => '' // pode virar campo no BD depois
        ];
    }

    if ($row['categoria'] === 'prova') {
        $simulados[$ano]['provas'][] = $row;
    } else {
        $simulados[$ano]['gabaritos'][] = $row;
    }
}

// Alterna esquerda/direita na timeline
$anosOrdenados = array_keys($simulados);
?>
<section class="content-card">
  <div class="content-header">
    <h1>Baixe todas as provas e gabaritos dos últimos anos:</h1>
    <p>
      Os arquivos abaixo são carregados automaticamente do banco de dados do AXIS.
      Basta clicar e baixar as provas oficiais do ENEM.
    </p>
  </div>

  <div class="timeline">
    <?php
    $i = 0;
    foreach ($anosOrdenados as $ano):
        $item = $simulados[$ano];
        $lado = ($i % 2 === 0) ? 'right' : 'left';
    ?>
      <article class="timeline-item <?php echo $lado; ?>">
        <div class="timeline-dot"></div>

        <?php if ($lado === 'right'): ?>
          <div class="timeline-year"><?php echo htmlspecialchars($ano); ?></div>
        <?php endif; ?>

        <div class="timeline-card">
          <div class="timeline-card-column">
            <span class="timeline-card-label">Provas</span>
            <div class="timeline-card-title">
              ENEM <?php echo htmlspecialchars($ano); ?>
            </div>
            <div class="download-list">
              <?php foreach ($item['provas'] as $prova): ?>
                <a href="<?php echo htmlspecialchars($prova['url']); ?>" class="download-link" target="_blank">
                  <span class="icon">⬇</span>
                  <?php echo htmlspecialchars($prova['label']); ?>
                </a>
              <?php endforeach; ?>
              <?php if (empty($item['provas'])): ?>
                <span class="download-link">
                  <span class="icon">⏳</span>
                  Provas ainda não cadastradas
                </span>
              <?php endif; ?>
            </div>
          </div>

          <div class="timeline-card-column">
            <span class="timeline-card-label">Gabaritos</span>
            <div class="timeline-card-title">
              Gabaritos oficiais <?php echo htmlspecialchars($ano); ?>
            </div>
            <div class="download-list">
              <?php foreach ($item['gabaritos'] as $gab): ?>
                <a href="<?php echo htmlspecialchars($gab['url']); ?>" class="download-link" target="_blank">
                  <span class="icon">✅</span>
                  <?php echo htmlspecialchars($gab['label']); ?>
                </a>
              <?php endforeach; ?>
              <?php if (empty($item['gabaritos'])): ?>
                <span class="download-link">
                  <span class="icon">⏳</span>
                  Gabaritos ainda não cadastrados
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="mascot">
          <?php if (!empty($item['mascote'])): ?>
            <img src="<?php echo htmlspecialchars($item['mascote']); ?>" alt="Mascote AXIS <?php echo htmlspecialchars($ano); ?>">
          <?php endif; ?>
        </div>

        <?php if ($lado === 'left'): ?>
          <div class="timeline-year"><?php echo htmlspecialchars($ano); ?></div>
        <?php endif; ?>
      </article>
    <?php
      $i++;
    endforeach;
    ?>
  </div>

  <div class="helper-footer">
    Dica AXIS: comece pelos anos mais recentes e, aos poucos, volte para
    <strong>provas mais antigas</strong> para acompanhar a evolução do exame.
  </div>
</section>

<?php
require __DIR__ . '/includes/footer.php';
