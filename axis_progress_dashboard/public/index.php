<?php
// public/index.php
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seu progresso - AXIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
  <header class="app-header">
    <div class="brand">
      <div class="globe">🌐</div>
      <h1>Seu progresso</h1>
    </div>
    <nav class="nav">
      <a class="nav-link" href="#">Início</a>
      <a class="nav-link" href="#">Matérias</a>
      <a class="nav-link" href="#">Comunidade</a>
      <a class="nav-link" href="#">Perfil</a>
      <button class="hamburger" aria-label="menu">☰</button>
    </nav>
  </header>

  <main class="grid">
    <section class="card about">
      <h2>Como calculamos seu progresso?</h2>
      <p>Somamos atividades por semana (aulas assistidas, posts na comunidade e questões respondidas) e
      comparamos com a semana anterior para mostrar a sua evolução.
    </section>

    <section class="card donut">
      <h2>Questões respondidas:</h2>
      <div class="donut-wrap"><canvas id="donutChart" width="320" height="320"></canvas></div>
      <ul class="legend">
        <li><span class="dot up"></span> <strong class="accent">+X</strong> questões a mais que a semana passada.</li>
        <li><span class="dot down"></span> <strong class="muted">-Y</strong> questões em relação à semana passada.</li>
      </ul>
    </section>

    <section class="card bar">
      <h3>Aulas assistidas por dia essa semana:</h3>
      <canvas id="barChart" height="140"></canvas>
    </section>

    <section class="card line">
      <h3>Posts enviados na comunidade:</h3>
      <canvas id="lineChart" height="140"></canvas>
    </section>
  </main>

  <footer class="footer">
    <p>AXIS</p>
  </footer>

<script>
const API_URL = '../backend/api/metrics.php';
const weekdayLabels = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];

async function loadData() {
  const res = await fetch(API_URL);
  const data = await res.json();
  // Normalize to 7 days
  function toSeries(rows) {
    const map = new Map(rows.map(r => [Number(r.weekday), Number(r.lessons || r.posts)]));
    return [1,2,3,4,5,6,7].map(d => map.get(d) || 0);
  }
  const lessonsSeries = toSeries(data.lessons_week);
  const postsSeries   = toSeries(data.posts_week);
  const thisWeek = Number(data.questions_week.this_week || 0);
  const lastWeek = Number(data.questions_week.last_week || 0);

  // Donut
  const ctxD = document.getElementById('donutChart');
  const delta = thisWeek - lastWeek;
  const up = Math.max(delta, 0);
  const down = Math.max(-delta, 0);

  new Chart(ctxD, {
    type: 'doughnut',
    data: {
      labels: ['Esta semana', 'Semana passada'],
      datasets: [{
        data: [thisWeek, lastWeek],
        backgroundColor: ['#FFEB3B', '#0F3B6F'],
        borderWidth: 0,
        hoverOffset: 4
      }]
    },
    options: {
      cutout: '70%',
      plugins: { legend: { display: false } }
    }
  });

  // Update legend numbers
  document.querySelector('.legend .accent').textContent = (up||0);
  document.querySelector('.legend .muted').textContent  = (down||0);

  // Bar
  const ctxB = document.getElementById('barChart');
  new Chart(ctxB, {
    type: 'bar',
    data: {
      labels: weekdayLabels.slice(0,6),
      datasets: [{
        label: 'Aulas',
        data: lessonsSeries.slice(0,6),
        backgroundColor: '#FF79B0',
        borderRadius: 8
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      },
      plugins: { legend: { display: false } }
    }
  });

  // Line
  const ctxL = document.getElementById('lineChart');
  new Chart(ctxL, {
    type: 'line',
    data: {
      labels: weekdayLabels.slice(0,6),
      datasets: [{
        label: 'Posts',
        data: postsSeries.slice(0,6),
        tension: 0.4,
        fill: false,
        borderWidth: 3,
        pointRadius: 4,
        borderColor: '#FF79B0',
        pointBackgroundColor: '#FF79B0'
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

loadData();
</script>
</body>
</html>
