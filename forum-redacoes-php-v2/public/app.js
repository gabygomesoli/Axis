let state = { page: 1, per_page: 5, q: '' };
const MAX_COLS = 30, MAX_LINES = 30;

async function api(u, o = {}) {
  const r = await fetch(
    u,
    Object.assign({ headers: { 'Content-Type': 'application/json' } }, o)
  );
  if (!r.ok) {
    const e = await r.json().catch(() => ({ error: r.statusText }));
    throw new Error(e.error || 'Erro');
  }
  return r.json();
}

const qs = (s, e = document) => e.querySelector(s);

function sanitize(t) {
  return (t || '').replace(/[&<>]/g, s => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[s]
  ));
}

function hardWrap(text) {
  const out = [];
  const lines = text.replace(/\t/g, '    ').split(/\r?\n/);
  for (let ln of lines) {
    while (ln.length > MAX_COLS) {
      out.push(ln.slice(0, MAX_COLS));
      ln = ln.slice(MAX_COLS);
      if (out.length >= MAX_LINES) break;
    }
    if (out.length >= MAX_LINES) break;
    out.push(ln);
    if (out.length >= MAX_LINES) break;
  }
  return out.slice(0, MAX_LINES).join('\n');
}

async function renderAuth() {
  const el = qs('#auth');
  const me = await api('../api/me.php').catch(() => ({ user: null }));

  if (me.user) {
    el.innerHTML = `
      <div class="row">
        <img src="${me.user.avatar_url || 'https://avatars.githubusercontent.com/u/0?v=4'}"
             style="width:32px;height:32px;border-radius:50%;object-fit:cover">
        <span>Olá, <strong>${me.user.name}</strong> (@${me.user.username})</span>
        <button class="secondary" id="logout">Sair</button>
      </div>`;
    qs('#compose').style.display = 'block';

    qs('#logout').onclick = async () => {
      await api('../api/logout.php', { method: 'POST', body: '{}' });
      location.reload();
    };
  } else {
    el.innerHTML = `
      <div class="row">
        <input id="name" placeholder="Nome"/>
        <input id="username" placeholder="Usuário"/>
        <input id="password" placeholder="Senha" type="password"/>
        <button id="register">Cadastrar</button>
        <button class="secondary" id="login">Entrar</button>
      </div>`;
    qs('#compose').style.display = 'none';

    qs('#register').onclick = async () => {
      try {
        await api('../api/register.php', {
          method: 'POST',
          body: JSON.stringify({
            name: qs('#name').value.trim(),
            username: qs('#username').value.trim(),
            password: qs('#password').value
          })
        });
        alert('Usuário criado! Faça login.');
      } catch (e) {
        alert(e.message);
      }
    };

    qs('#login').onclick = async () => {
      try {
        await api('../api/login.php', {
          method: 'POST',
          body: JSON.stringify({
            username: qs('#username').value.trim(),
            password: qs('#password').value
          })
        });
        location.reload();
      } catch (e) {
        alert(e.message);
      }
    };
  }
}

function updateCounters() {
  const txt = qs('#essay-content').value;
  const lines = txt.split(/\r?\n/).length;
  qs('#count-ch').textContent = txt.length;
  qs('#count-ln').textContent = lines;
}

async function loadEssays() {
  const list = qs('#essays');
  const p = new URLSearchParams({ page: state.page, per_page: state.per_page });
  if (state.q) p.set('q', state.q);

  const data = await api('../api/essays.php?' + p.toString());
  list.innerHTML = '';

  if (data.essays.length === 0) {
    list.innerHTML = '<p class="muted">Nenhuma redação por enquanto.</p>';
  } else {
    for (const e of data.essays) {
      const card = document.createElement('div');
      card.className = 'card';
      const date = new Date(e.created_at.replace(' ', 'T')).toLocaleString();

      card.innerHTML = `
        <div class="row" style="justify-content:space-between;align-items:center;">
          <div class="row">
            <img src="${e.avatar_url || 'https://avatars.githubusercontent.com/u/0?v=4'}"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover">
            <div>
              <div>
                <span class="username">${sanitize(e.name)}</span>
                <span class="muted">@${sanitize(e.username)} • ${date}</span>
              </div>
              <div><strong>${sanitize(e.title)}</strong></div>
            </div>
          </div>
          <div class="actions">
            <button class="like-btn ${e.liked_by_me ? 'liked' : ''}" data-id="${e.id}">
              Curtir (${e.like_count})
            </button>
            <button class="secondary show-comments" data-id="${e.id}">
              Comentários (${e.comment_count})
            </button>
          </div>
        </div>
        <div class="almaco preview" style="margin-top:.75rem;max-width:66ch;">
          ${sanitize(e.content)}
        </div>
        <div id="c_${e.id}" style="display:none;margin-top:.5rem;"></div>`;

      list.appendChild(card);
    }
  }

  const totalPages = Math.max(1, Math.ceil(data.total / data.per_page));
  qs('#page-info').textContent =
    `Página ${data.page} de ${totalPages} — ${data.total} redações`;
  qs('#prev').disabled = data.page <= 1;
  qs('#next').disabled = data.page >= totalPages;

  document.querySelectorAll('.like-btn').forEach(b => {
    b.onclick = async () => {
      const id = Number(b.dataset.id);
      const r = await api('../api/like.php', {
        method: 'POST',
        body: JSON.stringify({ essay_id: id })
      });
      b.textContent = `Curtir (${r.like_count})`;
      b.classList.toggle('liked');
    };
  });

  document.querySelectorAll('.show-comments').forEach(b => {
    b.onclick = () => toggleComments(Number(b.dataset.id));
  });
}

async function toggleComments(id) {
  const box = qs('#c_' + id);
  if (box.style.display === 'none') {
    await loadComments(id);
    box.style.display = 'block';
  } else {
    box.style.display = 'none';
  }
}

async function loadComments(id) {
  const box = qs('#c_' + id);
  const r = await api('../api/comments.php?essay_id=' + id);

  let html = `
    <div class="row">
      <input id="newc_${id}" placeholder="Escreva um comentário..." style="flex:1"/>
      <button id="sendc_${id}">Enviar</button>
    </div>`;

  for (const c of r.comments) {
    const when = new Date(c.created_at.replace(' ', 'T')).toLocaleString();
    html += `
      <div class="card" style="padding:.5rem 1rem;">
        <div>
          <span class="username">${sanitize(c.name)}</span>
          <span class="muted">@${sanitize(c.username)} • ${when}</span>
        </div>
        <div style="white-space:pre-wrap;margin-top:.25rem;">
          ${sanitize(c.content)}
        </div>
      </div>`;
  }

  box.innerHTML = html;

  qs('#sendc_' + id).onclick = async () => {
    const content = qs('#newc_' + id).value.trim();
    if (!content) return;
    await api('../api/comments.php', {
      method: 'POST',
      body: JSON.stringify({ essay_id: id, content })
    });
    await loadComments(id);
  };
}

async function init() {
  await renderAuth();
  await loadEssays();

  const ta = qs('#essay-content');
  ta.addEventListener('input', () => {
    updateCounters();
  });

  updateCounters();

  qs('#btn-publish').onclick = async () => {
    const title = qs('#essay-title').value.trim();
    let content = qs('#essay-content').value;

    if (!title || !content.trim()) {
      alert('Preencha título e conteúdo.');
      return;
    }

    content = hardWrap(content);

    await api('../api/essays.php', {
      method: 'POST',
      body: JSON.stringify({ title, content })
    });

    qs('#essay-title').value = '';
    qs('#essay-content').value = '';
    updateCounters();
    state.page = 1;

    await loadEssays();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  qs('#btn-search').onclick = () => {
    state.q = qs('#search').value.trim();
    state.page = 1;
    loadEssays();
  };

  qs('#prev').onclick = () => {
    if (state.page > 1) {
      state.page--;
      loadEssays();
    }
  };

  qs('#next').onclick = () => {
    state.page++;
    loadEssays();
  };
}

init();

// ======= NOVOS RECURSOS =======
async function loadTemplates() {
  const box = document.createElement('div');
  box.className = 'card';
  const data = await api('../api/templates.php');

  let html = '<h3>Modelos de redação</h3>';
  html += '<div class="row">';
  html += '<select id="tplSelect"><option value="">-- escolher --</option>' +
    data.templates.map(t => `<option value="${t.id}">${t.title}</option>`).join('') +
    '</select>';
  html += '<button id="tplApply" class="">Aplicar</button>';
  html += '</div><div id="tplPreview" class="muted" style="margin-top:.5rem"></div>';
  box.innerHTML = html;

  const compose = document.getElementById('compose');
  compose.parentNode.insertBefore(box, compose);

  const sel = box.querySelector('#tplSelect');
  sel.onchange = async () => {
    const id = Number(sel.value || 0);
    if (!id) {
      box.querySelector('#tplPreview').innerHTML = '';
      return;
    }
    const t = await api('../api/templates.php?id=' + id);
    box.querySelector('#tplPreview').innerHTML =
      `<strong>Proposta:</strong> ${sanitize(t.template.prompt)}<br/>
       <details><summary>Coletânea</summary>
         <pre style="white-space:pre-wrap">${sanitize(t.template.collection_text || '')}</pre>
       </details>`;
  };

  box.querySelector('#tplApply').onclick = async () => {
    const id = Number(sel.value || 0);
    if (!id) return;
    const t = await api('../api/templates.php?id=' + id);
    const ta = document.getElementById('essay-content');
    const prefix = `Proposta: ${t.template.prompt}\n` +
      (t.template.collection_text ? `[Coletânea resumida]\n` : '');
    ta.value = (prefix + '\n' + ta.value).trim();
  };
}

async function openRubric(essayId) {
  const data = await api('../api/rubric.php?essay_id=' + essayId);
  const scores = data.scores;
  const auto = data.auto || { comp1: '', comp2: '', comp3: '', comp4: '', comp5: '' };

  const div = document.createElement('div');
  div.className = 'card';
  div.innerHTML = `
    <h3>Rubrica ENEM</h3>
    <table>
      <tr><th>Comp.</th><th>Auto</th><th>Nota</th></tr>
      ${[1, 2, 3, 4, 5].map(i => `
        <tr>
          <td>C${i}</td>
          <td>${auto['comp' + i] ?? (scores ? scores['auto_comp' + i] : '')}</td>
          <td>
            <input id="c${i}" type="number" min="0" max="200"
                   value="${scores ? (scores['comp' + i] || '') : ''}">
          </td>
        </tr>`).join('')}
    </table>
    <div class="row" style="justify-content:flex-end;margin-top:.5rem">
      <button id="saveRubric">Salvar notas</button>
    </div>`;

  document.body.insertBefore(div, document.body.firstChild);

  div.querySelector('#saveRubric').onclick = async () => {
    const payload = { essay_id: essayId };
    [1, 2, 3, 4, 5].forEach(i => {
      payload['comp' + i] = Number(div.querySelector('#c' + i).value || 0);
    });
    const res = await api('../api/rubric.php', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    alert('Rubrica salva. Total: ' + res.total);
  };
}

function addExtraControls(card, essay) {
  const toolbar = document.createElement('div');
  toolbar.className = 'row';
  toolbar.style.marginTop = '.5rem';

  toolbar.innerHTML = `
    <a href="../api/export_pdf.php?essay_id=${essay.id}" target="_blank">
      <button class="secondary">Exportar PDF (almaço)</button>
    </a>
    <button class="secondary rubric-btn" data-id="${essay.id}">Rubrica ENEM</button>
    <label class="secondary" style="padding:.4rem .6rem; cursor:pointer;">
      Upload folha manuscrita
      <input type="file" id="scan_${essay.id}" accept="image/png,image/jpeg" style="display:none">
    </label>
    <span class="muted">${essay.scan_path ? 'Imagem anexada' : ''}</span>
    <span class="muted">Status: ${essay.status}</span>
    <span class="mod-tools" style="display:none">
      <button data-st="em_correcao" data-id="${essay.id}" class="secondary stbtn">Em correção</button>
      <button data-st="corrigida" data-id="${essay.id}" class="secondary stbtn">Corrigida</button>
      <button data-st="publicada" data-id="${essay.id}" class="secondary stbtn">Publicada</button>
    </span>`;

  card.appendChild(toolbar);

  toolbar.querySelector('.rubric-btn').onclick = () => openRubric(essay.id);

  const input = toolbar.querySelector('#scan_' + essay.id);
  input.onchange = async (e) => {
    if (!e.target.files.length) return;
    const fd = new FormData();
    fd.append('essay_id', essay.id);
    fd.append('scan', e.target.files[0]);
    const res = await fetch('../api/upload_scan.php', { method: 'POST', body: fd });
    if (!res.ok) { alert('Falha no upload'); return; }
    alert('Upload OK');
  };

  toolbar.querySelectorAll('.stbtn').forEach(btn => {
    btn.onclick = async () => {
      try {
        await api('../api/status.php', {
          method: 'POST',
          body: JSON.stringify({ essay_id: essay.id, status: btn.dataset.st })
        });
        alert('Status atualizado');
        loadEssays();
      } catch (e) { alert(e.message); }
    };
  });
}

// Patch existente para adicionar controles extras
const _origLoadEssays = loadEssays;
loadEssays = async function () {
  await _origLoadEssays();
  const p = new URLSearchParams({ page: state.page, per_page: state.per_page });
  if (state.q) p.set('q', state.q);
  const data = await api('../api/essays.php?' + p.toString());
  const list = document.getElementById('essays');
  const cards = list.querySelectorAll('.card');
  data.essays.forEach((essay, i) => { addExtraControls(cards[i], essay); });
};

// Load templates UI on start
loadTemplates().catch(() => {});
