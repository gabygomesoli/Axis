<?php ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Ranking • AXIS</title>
  <link rel="stylesheet" href="css/styles.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <div class="tab">🌐 Ranking</div>
      <div class="tab" style="opacity:.6">Início</div>
      <div class="tab" style="opacity:.6">Matérias</div>
      <div class="tab" style="opacity:.6">Comunidade</div>
      <div class="tab" style="opacity:.6">Perfil</div>
    </div>
    <div class="grid">
      <div class="card">
        <h2>Objetivos</h2>
        <div class="row" id="goals"></div>
      </div>
      <div class="card leaderboard">
        <h2>Sua Posição</h2>
        <div id="leaderboard"></div>
      </div>
    </div>
    <div class="card badges">
      <h2>Insígnias</h2>
      <div class="badge-hint">As Insígnias são alcançadas conforme sua pontuação sobe! Ganhe curtidas e ajude a comunidade para desbloquear.</div>
      <div class="badge-row" id="badgeRow"></div>
    </div>
    <div class="footer">• AXIS • </div>
  </div>
  <script src="js/app.js"></script>
</body>
</html>
