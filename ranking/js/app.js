const API = '../backend/ranking_api.php';

// imagens de insígnias na pasta /ranking/img
// se tiver mais insígnias que imagens, o código reaproveita a sequência
const BADGE_IMAGES = [
  'Axis - Mascotes (2).png',
  'Axis - Mascotes (3).png',
  'Axis - Mascotes (4).png',
  'Axis - Mascotes (5).png',
  'Axis - Mascotes (6).png',
  'Axis - Mascotes (7).png',
  'Axis - Mascotes (8).png',
  'Axis - Mascotes (9).png'
];

const CenterText = {
  id: 'centerText',
  afterDraw(chart, _args, opts) {
    const { ctx, chartArea } = chart;
    if (!chartArea) return;
    const { width, height } = chartArea;

    ctx.save();
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    ctx.fillStyle = '#ffffff';
    ctx.font = '700 20px Inter, system-ui, sans-serif';
    ctx.fillText(opts.main || '', width / 2, height / 2 - 6);

    if (opts.sub) {
      ctx.fillStyle = '#d1d5db';
      ctx.font = '600 11px Inter, system-ui, sans-serif';
      ctx.fillText(opts.sub, width / 2, height / 2 + 14);
    }
    ctx.restore();
  }
};

async function fetchAll() {
  const res = await fetch(`${API}?action=all`, {
    credentials: 'same-origin'
  });
  const json = await res.json().catch(() => ({}));
  if (!json.ok) {
    throw new Error(json.error || 'Erro na API de ranking.');
  }
  return json.data;
}

function createDonut(canvas, color, current, target, label) {
  return new Chart(canvas.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Atual', 'Restante'],
      datasets: [{
        data: [current, Math.max(0, target - current)],
        backgroundColor: [color, 'rgba(255,255,255,0.08)'],
        borderWidth: 0,
        cutout: '72%'
      }]
    },
    options: {
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
        centerText: {
          main: `${current} de ${target}`,
          sub: label
        }
      }
    },
    plugins: [CenterText]
  });
}

function renderGoals(goals) {
  const wrap = document.querySelector('#goals');
  if (!wrap) return;
  wrap.innerHTML = '';

  goals.forEach(g => {
    const card = document.createElement('div');
    card.className = 'goal';

    card.innerHTML = `
      <canvas></canvas>
      <small>${g.title}</small>
    `;

    wrap.appendChild(card);
    const canvas = card.querySelector('canvas');
    createDonut(
      canvas,
      g.color,
      Number(g.current_value) || 0,
      Number(g.target_value) || 0,
      'progresso'
    );
  });
}

function renderLeaderboard(list, currentUserId) {
  const wrap = document.querySelector('#leaderboard');
  if (!wrap) return;
  wrap.innerHTML = '';

  if (!list.length) {
    wrap.innerHTML = '<p class="empty-state">Ainda não há dados suficientes para montar o ranking desta semana.</p>';
    return;
  }

  list.forEach((u, index) => {
    const me = u.id === currentUserId ? ' (Você)' : '';
    const div = document.createElement('div');
    div.className = 'entry';

    const pos = String(index + 1).padStart(2, '0');
    const trendClass = u.trend > 0 ? 'up' : (u.trend < 0 ? 'down' : 'eq');
    const trendArrow = u.trend > 0 ? '↑' : (u.trend < 0 ? '↓' : '→');

    div.innerHTML = `
      <div class="rank">${pos}</div>
      <div class="name">${u.name}${me}</div>
      <div class="trend ${trendClass}">
        <span>${trendArrow}</span>
        <span>${u.score} pts</span>
      </div>
    `;

    wrap.appendChild(div);
  });
}

function renderBadges(badges) {
  const row = document.querySelector('#badgeRow');
  if (!row) return;
  row.innerHTML = '';

  if (!badges.length) {
    row.innerHTML = '<p class="empty-state">Nenhuma insígnia configurada.</p>';
    return;
  }

  badges.forEach((b, index) => {
    const div = document.createElement('div');
    div.className = 'badge' + (b.earned_at ? ' earned' : '');
    div.title = `${b.title}${b.earned_at ? ' • conquistada' : ' • bloqueada'}`;

    // escolhe imagem pela posição
    const imgName = BADGE_IMAGES[index % BADGE_IMAGES.length];
    const imgPath = `img/${imgName}`;

    div.innerHTML = `
      <img src="${imgPath}" alt="Insígnia: ${b.title}">
    `;

    row.appendChild(div);
  });
}

function renderWeekLabel(week) {
  const labelEl = document.querySelector('#weekLabel');
  if (!labelEl || !week) return;

  const start = week.start;
  const end = week.end;

  labelEl.textContent = `Período considerado: ${start} até ${end}`;
}

async function init() {
  try {
    const data = await fetchAll();
    const { user, leaderboard, goals, badges, week } = data;

    renderGoals(goals || []);
    renderLeaderboard(leaderboard || [], user.id);
    renderBadges(badges || []);
    renderWeekLabel(week);
  } catch (err) {
    console.error(err);
    const wrap = document.querySelector('#leaderboard');
    if (wrap) {
      wrap.innerHTML = `<p class="error-state">Não foi possível carregar o ranking agora. Tente novamente mais tarde.</p>`;
    }
  }
}

document.addEventListener('DOMContentLoaded', init);
