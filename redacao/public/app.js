document.addEventListener('DOMContentLoaded', () => {
  const state = { page: 1, per_page: 5, q: '' };
  const MAX_LINES = 30;

  const qs = (s, el = document) => el.querySelector(s);

  const UPLOADS_BASE = "../../";

  function resolveAvatar(path) {
    if (!path) {
      return "../../img/perfilpadrao.png";
    }
    if (/^https?:\/\//i.test(path)) {
      return path;
    }
    return UPLOADS_BASE + path.replace(/^\/+/, "");
  }

  async function api(url, opts = {}) {
    const res = await fetch(url, {
      ...opts,
      headers: {
        "Content-Type": "application/json",
        ...(opts.headers || {})
      },
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({ error: res.statusText }));
      throw new Error(err.error || "Erro na comunicação com o servidor");
    }
    return res.json();
  }

  function sanitize(text) {
    return (text || "").replace(/[&<>]/g, (s) => (
      { "&": "&amp;", "<": "&lt;", ">": "&gt;" }[s]
    ));
  }

  function updateCounters() {
    const txt = qs('#red-text').value;
    const lines = txt.split(/\r?\n/).length;
    qs('#count-ch').textContent = txt.length;
    qs('#count-ln').textContent = lines;
  }

  (function setupNavbar() {
    const navbar = qs('.navbar');
    const navbarToggle = qs('.navbar-toggle');
    const mobileMenu = qs('.mobile-menu');
    const menuOverlay = qs('.menu-overlay');

    if (navbarToggle && mobileMenu && menuOverlay) {
      const toggleMenu = (open = null) => {
        const willOpen = open === null
          ? !mobileMenu.classList.contains("active")
          : open;

        navbarToggle.classList.toggle("active", willOpen);
        mobileMenu.classList.toggle("active", willOpen);
        menuOverlay.classList.toggle("active", willOpen);
        document.body.style.overflow = willOpen ? "hidden" : "";
      };

      navbarToggle.addEventListener("click", () => toggleMenu());
      menuOverlay.addEventListener("click", () => toggleMenu(false));
      document
        .querySelectorAll(".mobile-menu a")
        .forEach((a) => a.addEventListener("click", () => toggleMenu(false)));

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && mobileMenu.classList.contains("active")) {
          toggleMenu(false);
        }
      });

      let lastScroll = 0;
      window.addEventListener("scroll", () => {
        const currentScroll = window.scrollY;
        if (currentScroll > 10) {
          navbar.classList.add("scrolled");
        } else {
          navbar.classList.remove("scrolled");
        }
        if (currentScroll > lastScroll && currentScroll > 100) {
          navbar.classList.add("hidden");
        } else {
          navbar.classList.remove("hidden");
        }
        lastScroll = currentScroll;
      });
    }
  })();

  const btnProfile = qs('#btn-profile');
  if (btnProfile) {
    btnProfile.addEventListener('click', () => {
      window.location.href = "../../perfil/index.php";
    });
  }

  const btnScrollCompose = qs('#btn-scroll-compose');
  if (btnScrollCompose) {
    btnScrollCompose.addEventListener('click', () => {
      const card = qs('#compose-card');
      if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  async function loadRedacoes() {
    const list = qs('#redacoes');
    const params = new URLSearchParams({
      page: state.page,
      per_page: state.per_page
    });
    if (state.q) params.set('q', state.q);

    list.innerHTML = '<p class="muted" style="padding:16px;">Carregando redações...</p>';

    let data;
    try {
      data = await api('../api/redacoes.php?' + params.toString());
    } catch (e) {
      list.innerHTML = '<p class="muted" style="padding:16px;">Erro ao carregar redações.</p>';
      console.error(e);
      return;
    }

    list.innerHTML = '';

    if (!data.redacoes || data.redacoes.length === 0) {
      list.innerHTML = '<p class="muted" style="padding:16px;">Nenhuma redação por enquanto.</p>';
    } else {
      data.redacoes.forEach((r) => {
        const card = document.createElement('div');
        card.className = 'red-card';

        const avatar = resolveAvatar(r.avatar_url);
        const date = new Date(r.created_at.replace(' ', 'T')).toLocaleString('pt-BR');

        card.innerHTML = `
          <div class="red-header">
            <div class="red-user">
              <img src="${avatar}" alt="@${sanitize(r.username)}" class="red-avatar">
              <div class="red-meta">
                <span class="username">${sanitize(r.name)}</span>
                <span class="muted">@${sanitize(r.username)} • ${date}</span>
                <span class="red-title">${sanitize(r.titulo)}</span>
              </div>
            </div>
            <div class="red-actions">
              <button class="btn-secondary like-btn ${r.liked_by_me ? 'liked' : ''}" data-id="${r.id}">
                Curtir (${r.like_count})
              </button>
              <button class="btn-secondary show-comments" data-id="${r.id}">
                Comentários (${r.comment_count})
              </button>
              <a href="../api/export_pdf.php?redacao_id=${r.id}" target="_blank">
                <button class="btn-secondary">PDF almaço</button>
              </a>
            </div>
          </div>
          <div class="red-footer">
            <div class="almaco preview">
              ${sanitize(r.texto)}
            </div>
            <div class="red-comments" id="c_${r.id}" style="display:none;"></div>
          </div>
        `;

        list.appendChild(card);
      });
    }

    const totalPages = Math.max(1, Math.ceil(data.total / data.per_page));
    qs('#page-info').textContent =
      `Página ${data.page} de ${totalPages} — ${data.total} redações`;

    qs('#prev').disabled = data.page <= 1;
    qs('#next').disabled = data.page >= totalPages;

    // eventos de like
    document.querySelectorAll('.like-btn').forEach((btn) => {
      btn.onclick = async () => {
        const id = Number(btn.dataset.id);
        try {
          const r = await api('../api/like.php', {
            method: 'POST',
            body: JSON.stringify({ redacao_id: id })
          });
          btn.textContent = `Curtir (${r.like_count})`;
          btn.classList.toggle('liked');
        } catch (e) {
          alert(e.message);
        }
      };
    });

    document.querySelectorAll('.show-comments').forEach((btn) => {
      btn.onclick = () => {
        const id = Number(btn.dataset.id);
        toggleComments(id);
      };
    });
  }

  async function toggleComments(id) {
    const box = qs('#c_' + id);
    if (!box) return;
    if (box.style.display === 'none' || box.style.display === '') {
      await loadComments(id);
      box.style.display = 'block';
    } else {
      box.style.display = 'none';
    }
  }

  async function loadComments(id) {
    const box = qs('#c_' + id);
    box.innerHTML = '<p class="muted">Carregando comentários...</p>';

    let data;
    try {
      data = await api('../api/comments.php?redacao_id=' + id);
    } catch (e) {
      box.innerHTML = '<p class="muted">Erro ao carregar comentários.</p>';
      console.error(e);
      return;
    }

    let html = `
      <div class="comment-input-row">
        <input id="newc_${id}" placeholder="Escreva um comentário..." />
        <button id="sendc_${id}" class="btn-primary">Enviar</button>
      </div>
    `;

    (data.comments || []).forEach((c) => {
      const when = new Date(c.created_at.replace(' ', 'T')).toLocaleString('pt-BR');
      html += `
        <div class="comment-card">
          <div>
            <span class="username">${sanitize(c.name)}</span>
            <span class="muted">@${sanitize(c.username)} • ${when}</span>
          </div>
          <div style="white-space:pre-wrap;margin-top:.25rem;">
            ${sanitize(c.content)}
          </div>
        </div>
      `;
    });

    box.innerHTML = html;

    qs('#sendc_' + id).onclick = async () => {
      const content = qs('#newc_' + id).value.trim();
      if (!content) return;
      try {
        await api('../api/comments.php', {
          method: 'POST',
          body: JSON.stringify({ redacao_id: id, content })
        });
        await loadComments(id);
      } catch (e) {
        alert(e.message);
      }
    };
  }

  const txtArea = qs('#red-text');
  if (txtArea) {
    txtArea.addEventListener('input', () => {
      const lines = txtArea.value.split(/\r?\n/);
      if (lines.length > MAX_LINES) {
      }
      updateCounters();
    });
    updateCounters();
  }

  const btnPublish = qs('#btn-publish');
  if (btnPublish) {
    btnPublish.onclick = async () => {
      const title = qs('#red-title').value.trim();
      const text  = qs('#red-text').value;

      if (!title || !text.trim()) {
        alert('Preencha título e texto da redação.');
        return;
      }

      try {
        await api('../api/redacoes.php', {
          method: 'POST',
          body: JSON.stringify({ titulo: title, texto: text })
        });
        qs('#red-title').value = '';
        qs('#red-text').value = '';
        updateCounters();
        state.page = 1;
        await loadRedacoes();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } catch (e) {
        alert(e.message);
      }
    };
  }

  const searchInput = qs('#search');
  const searchBtn = qs('#btn-search');

  if (searchBtn) {
    searchBtn.onclick = () => {
      state.q = (searchInput?.value || '').trim();
      state.page = 1;
      loadRedacoes();
    };
  }

  if (searchInput) {
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        searchBtn?.click();
      }
    });
  }

  const btnPrev = qs('#prev');
  const btnNext = qs('#next');

  if (btnPrev) {
    btnPrev.onclick = () => {
      if (state.page > 1) {
        state.page--;
        loadRedacoes();
      }
    };
  }

  if (btnNext) {
    btnNext.onclick = () => {
      state.page++;
      loadRedacoes();
    };
  }

  loadRedacoes().catch((e) => console.error(e));
});
