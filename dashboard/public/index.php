<?php
$paginaAtual = 'Estatísticas';
?>
<!DOCTYPE html>
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

  <nav class="navbar">
    <div class="logo-section">
      <img src="../../img/imgnavbar/globoaxis.png" alt="Logo do site" class="logo">
      <a href="#" class="page-title"><?php echo $paginaAtual; ?></a>
    </div>

    <div class="nav-links">
      <div class="navopcoes">
        <a href="../../home/home.php">Início</a>
        <a href="../../materias/materias.php">Matérias</a>
        <a href="../../comunidade/public/">Comunidade</a>
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
    <a href="../../comunidade/public/">Comunidade</a>
    <a href="../../perfil/index.php">Perfil</a>

    <div class="menu-divider"></div>

    <a href="../../ranking/">Ranking</a>
    <a href="#" class="active">Estatísticas</a>
    <a href="../../redacao/public/">Redação</a>
    <a href="../../corretor/">Corretor por IA</a>
    <a href="../../simulado/">Simulados</a>
    <a href="../../cronograma/">Cronogramas</a>

    <a href="../../autenticar/logout.php" class="logout-link">Sair</a>
  </div>

  <div class="menu-overlay"></div>

  <main class="grid">
    <section class="card about">
      <h2>Como calculamos seu progresso?</h2>
      <div class="about-content">
        <p>
          Somamos suas atividades por semana (aulas assistidas, posts na comunidade
          e questões respondidas) e comparamos com a semana anterior para mostrar
          sua evolução de forma clara e gamificada.
        </p>
        <img
          src="../../img/mascote.gif"
          alt="Mascote AXIS comemorando seu progresso"
          class="about-mascot">
      </div>
    </section>

    <section class="card donut">
      <h2>Questões respondidas semana vs semana:</h2>
      <div class="donut-wrap">
        <canvas id="donutChart" width="480" height="480"></canvas>
      </div>
      <ul class="legend" aria-live="polite">
        <li class="legend-totals">
          <strong>Esta semana:</strong> <span id="legendThis">0</span> •
          <strong>Semana passada:</strong> <span id="legendLast">0</span>
        </li>
      </ul>
    </section>

    <section class="card bar">
      <h3>Aulas assistidas por dia essa semana:</h3>
      <canvas id="barChart" height="300"></canvas>
    </section>

    <section class="card line">
      <h3>Posts enviados na comunidade:</h3>
      <canvas id="lineChart" height="300"></canvas>
    </section>
  </main>

  <script>

    const navbar = document.querySelector(".navbar");
    const toggleBtn = document.querySelector(".navbar-toggle");
    const mobileMenu = document.querySelector(".mobile-menu");
    const menuOverlay = document.querySelector(".menu-overlay");

    let lastScroll = 0;
    window.addEventListener("scroll", () => {
      const current = window.scrollY;

      if (current > lastScroll && current > 40) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
      lastScroll = current;
    });

    function openMenu() {
      if (!toggleBtn || !mobileMenu || !menuOverlay) return;
      toggleBtn.classList.add("active");
      mobileMenu.classList.add("active");
      menuOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }

    function closeMenu() {
      if (!toggleBtn || !mobileMenu || !menuOverlay) return;
      toggleBtn.classList.remove("active");
      mobileMenu.classList.remove("active");
      menuOverlay.classList.remove("active");
      document.body.style.overflow = "";
    }

    if (toggleBtn && mobileMenu && menuOverlay) {
      toggleBtn.addEventListener("click", () => {
        const isActive = toggleBtn.classList.contains("active");
        isActive ? closeMenu() : openMenu();
      });

      menuOverlay.addEventListener("click", closeMenu);

      window.addEventListener("resize", () => {
        if (window.innerWidth > 900) {
          closeMenu();
        }
      });
    }
  </script>

  <script>
    const API_URL = '../backend/api/metrics.php';
    const weekdayLabels = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];

    function fmtNumber(n) {
      return (new Intl.NumberFormat('pt-BR')).format(n);
    }

    const CenterText = {
      id: 'centerText',
      afterDraw(chart, _args, opts) {
        const { ctx, chartArea: { width, height } } = chart;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 42px Inter, system-ui, sans-serif';
        ctx.fillText(opts.main || '', width / 2, height / 2 - 6);
        ctx.fillStyle = '#d1d5db';
        ctx.font = '600 14px Inter, system-ui, sans-serif';
        if (opts.sub) ctx.fillText(opts.sub, width / 2, height / 2 + 20);
        if (opts.sub2) ctx.fillText(opts.sub2, width / 2, height / 2 + 40);
        ctx.restore();
      }
    };

    const ValueLabels = {
      id: 'valueLabels',
      afterDatasetsDraw(chart) {
        const { ctx } = chart;
        ctx.save();
        ctx.font = '600 12px Inter';
        ctx.fillStyle = '#ffffff';
        chart.data.datasets.forEach((ds, i) => {
          const meta = chart.getDatasetMeta(i);
          meta.data.forEach((el, idx) => {
            const val = ds.data[idx];
            if (val == null) return;
            const p = el.tooltipPosition();
            ctx.fillText(val, p.x, p.y - 10);
          });
        });
        ctx.restore();
      }
    };

    const HoverLine = {
      id: 'hoverLine',
      afterDatasetsDraw(chart) {
        const { ctx, tooltip } = chart;
        if (!tooltip || !tooltip._active || !tooltip._active.length) return;
        const x = tooltip._active[0].element.x;
        ctx.save();
        ctx.strokeStyle = 'rgba(255,255,255,.25)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x, chart.chartArea.top);
        ctx.lineTo(x, chart.chartArea.bottom);
        ctx.stroke();
        ctx.restore();
      }
    };

    async function loadData() {
      const res = await fetch(API_URL);
      const data = await res.json();

      const toSeries = (rows, field) => {
        const map = new Map((rows || []).map(r => [Number(r.weekday), Number(r[field])]));
        return [1, 2, 3, 4, 5, 6, 7].map(d => map.get(d) || 0);
      };

      const lessonsSeries = toSeries(data.lessons_week, 'lessons');
      const postsSeries = toSeries(data.posts_week, 'posts');

      const thisWeek = Number(data?.questions_week?.this_week || 0);
      const lastWeek = Number(data?.questions_week?.last_week || 0);

      const diff = thisWeek - lastWeek;

      let percMore = 0;
      if (lastWeek > 0) percMore = (diff / lastWeek) * 100;
      else percMore = thisWeek > 0 ? 100 : 0;

      const percMoreRounded = Math.round(percMore);
      const fill = Math.max(0, Math.min(100, percMoreRounded));

      const up = Math.max(diff, 0);
      const down = Math.max(-diff, 0);
      const upEl = document.querySelector('.legend .accent');
      const downEl = document.querySelector('.legend .muted');
      if (upEl) upEl.textContent = `+${up} ${up === 1 ? 'questão' : 'questões'} a mais que a semana passada.`;
      if (downEl) downEl.textContent = `-${down} ${down === 1 ? 'questão' : 'questões'} em relação à semana passada.`;
      const totThis = document.getElementById('legendThis');
      const totLast = document.getElementById('legendLast');
      if (totThis) totThis.textContent = fmtNumber(thisWeek);
      if (totLast) totLast.textContent = fmtNumber(lastWeek);

      const ctxD = document.getElementById('donutChart').getContext('2d');
      const grad = ctxD.createLinearGradient(0, 0, 0, 300);
      grad.addColorStop(0, '#ffeb3b');
      grad.addColorStop(1, '#ffeb3b');

      new Chart(ctxD, {
        type: 'doughnut',
        data: {
          datasets: [{
            data: [fill, 100 - fill],
            backgroundColor: [grad, '#ff79b0'],
            borderWidth: 0,
            borderRadius: 12,
            circumference: 360,
            rotation: -90
          }]
        },
        options: {
          cutout: '78%',
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              enabled: false
            }
          }
        },
        plugins: [CenterText]
      });

      const d = Chart.getChart('donutChart');
      const signDiff = diff > 0 ? '+' : diff < 0 ? '−' : '';
      const signPer = percMoreRounded > 0 ? '+' : percMoreRounded < 0 ? '−' : '';
      d.options.plugins.centerText = {
        main: `${signDiff}${Math.abs(diff)}`,
        sub: `${signPer}${Math.abs(percMoreRounded)}% vs sem. passada`,
        sub2: `${fmtNumber(thisWeek)} vs ${fmtNumber(lastWeek)} resp.`
      };
      d.update();

      const ctxB = document.getElementById('barChart').getContext('2d');
      new Chart(ctxB, {
        type: 'bar',
        data: {
          labels: weekdayLabels.slice(0, 7),
          datasets: [{
            label: 'Aulas',
            data: lessonsSeries.slice(0, 7),
            backgroundColor: '#FF79B0',
            borderRadius: 10,
            maxBarThickness: 36
          }]
        },
        options: {
          scales: {
            x: {
              ticks: {
                color: '#E5E7EB',
                font: {
                  weight: '600'
                }
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                color: '#E5E7EB',
                stepSize: 1
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: ctx => ` ${ctx.raw} aulas`
              }
            }
          }
        },
        plugins: [ValueLabels]
      });

      const ctxL = document.getElementById('lineChart').getContext('2d');
      const g = ctxL.createLinearGradient(0, 0, 0, 220);
      g.addColorStop(0, 'rgba(139,92,246,0.45)');
      g.addColorStop(1, 'rgba(139,92,246,0.05)');

      new Chart(ctxL, {
        type: 'line',
        data: {
          labels: weekdayLabels.slice(0, 7),
          datasets: [{
            label: 'Posts',
            data: postsSeries.slice(0, 7),
            tension: .45,
            fill: true,
            backgroundColor: g,
            borderColor: '#8B5CF6',
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#8B5CF6'
          }]
        },
        options: {
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              mode: 'index',
              intersect: false,
              padding: 10,
              callbacks: {
                label: ctx => ` ${ctx.raw} posts`
              }
            }
          },
          scales: {
            x: {
              ticks: {
                color: '#E5E7EB',
                font: {
                  weight: '600'
                }
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                color: '#E5E7EB'
              }
            }
          }
        },
        plugins: [HoverLine]
      });
    }

    loadData();
  </script>
</body>

</html>