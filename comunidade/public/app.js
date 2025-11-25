document.addEventListener("DOMContentLoaded", async () => {

  const navbar = document.querySelector(".navbar");
  const navbarToggle = document.querySelector(".navbar-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");
  const menuOverlay = document.querySelector(".menu-overlay");
  const currentPage = document.body.dataset.page || "";
  const state = { page: 1, per_page: 10, q: "" };

  const postModal = document.getElementById("post-modal");
  const closePost = document.getElementById("close-post");
  const publishBtn = document.getElementById("btnpublicar");
  const postContent = document.getElementById("post-content");

  const UPLOADS_BASE = "../../";

  const qs = (s, el = document) => el.querySelector(s);

  const sanitize = (text) =>
    text.replace(
      /[&<>]/g,
      (s) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;" }[s])
    );

  const linkifyMentions = (text) =>
    sanitize(text).replace(
      /@([A-Za-z0-9_.]+)/g,
      '<a href="#" class="mention" data-u="$1">@$1</a>'
    );

  const resolveAvatar = (path) => {
    if (!path) {
      return "../../img/perfilpadrao.png";
    }
    if (/^https?:\/\//i.test(path)) {
      return path;
    }
    return UPLOADS_BASE + path.replace(/^\/+/, "");
  };

  const api = async (url, opts = {}) => {
    const res = await fetch(url, {
      ...opts,
      headers: { "Content-Type": "application/json" },
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({ error: res.statusText }));
      throw new Error(err.error || "Erro de comunicação com o servidor");
    }
    return res.json();
  };

  if (navbarToggle && mobileMenu && menuOverlay) {
    // Abre/fecha o menu
    const toggleMenu = (open = null) => {
      const willOpen =
        open === null ? !mobileMenu.classList.contains("active") : open;

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
      if (e.key === "Escape" && mobileMenu.classList.contains("active"))
        toggleMenu(false);
    });
  }

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

  async function renderAuth() {
    const el = qs("#auth");
    if (!el) return;

    qs("#notif-btn").onclick = () => openNotifications(true);
    await refreshNotifBadge();
  }

  async function refreshNotifBadge() {
    try {
      const data = await api("../api/notifications.php");
      const badge = qs("#notif-badge");
      if (badge)
        badge.textContent = data.unread > 0 ? `(${data.unread} novas)` : "(0)";
    } catch (err) {
      console.warn("Erro ao atualizar badge:", err);
    }
  }

  function renderNotificationsList(items) {
    if (!items || items.length === 0)
      return '<p class="muted">Sem notificações.</p>';

    return items
      .map((n) => {
        const kind =
          n.type === "mention_post"
            ? "mencionou você em um post"
            : "mencionou você em um comentário";
        const when = new Date(n.created_at.replace(" ", "T")).toLocaleString();
        const avatar = resolveAvatar(n.actor_avatar);
        return `
          <div class="notif-card">
            <div class="notif-header">
              <img src="${avatar}" alt="@${n.actor_username}" class="notif-avatar">
              <div>
                <strong>@${n.actor_username}</strong> ${kind}
                <span class="muted">${when}</span>
              </div>
            </div>
          </div>
        `;
      })
      .join("");
  }

  async function openNotifications(forceOpen = true) {
    const modal = qs("#notif-modal");
    if (!modal) return;

    if (forceOpen && modal.classList.contains("active")) {
      modal.classList.remove("active");
      document.body.classList.remove("modal-open");
      return;
    }

    modal.classList.add("active");
    document.body.classList.add("modal-open");
    modal.innerHTML = `<div class="card notif-box"><p>Carregando notificações...</p></div>`;

    try {
      const data = await api("../api/notifications.php");
      modal.innerHTML = `
        <div class="card notif-box">
          <h3>Notificações</h3>
          <div class="notif-list">${renderNotificationsList(
            data.notifications
          )}</div>
          <div class="notif-footer">
            <button id="mark-all" class="btn-accent">Marcar todas como lidas</button>
          </div>
        </div>
      `;
      const markAll = qs("#mark-all");
      if (markAll) {
        markAll.onclick = async () => {
          await api("../api/notifications.php", {
            method: "POST",
            body: JSON.stringify({ action: "mark_read_all" }),
          });
          await refreshNotifBadge();
          openNotifications(false);
        };
      }
    } catch (err) {
      modal.innerHTML = `<div class="card notif-box"><p>Erro ao carregar notificações.</p></div>`;
      console.error("Erro ao abrir notificações:", err);
    }
  }

  async function loadPosts() {
    const list = qs("#posts");
    if (!list) return;

    const params = new URLSearchParams({
      page: state.page,
      per_page: state.per_page,
    });
    if (state.q) params.set("q", state.q);

    const data = await api("../api/posts.php?" + params.toString());
    list.innerHTML = "";

    if (!data.posts || data.posts.length === 0) {
      list.innerHTML = '<p class="muted">Nenhuma postagem por enquanto.</p>';
      return;
    }

    for (const p of data.posts) {
      const card = document.createElement("div");
      card.className = "post card";
      const avatar = resolveAvatar(p.avatar_url);

      card.innerHTML = `
        <div class="post-container">
          <img src="${avatar}" alt="avatar" class="post-avatar">
          <div class="post-content">
            <div class="post-header">
              <span class="post-name">${p.name}</span>
              <span class="post-username">@${p.username}</span>
            </div>

            <div class="post-text-bubble">
              ${linkifyMentions(p.content)}
            </div>

            <div class="post-actions">
              <button class="like-btn ${
                p.liked_by_me ? "liked" : ""
              }" data-id="${p.id}">
                <img src="../../img/btnlike${
                  p.liked_by_me ? "-filled" : ""
                }.png" class="like-icon">
              </button>
              <span class="like-count" id="lc_${p.id}">${p.like_count}</span>
              <button class="comment-btn show-comments" data-id="${p.id}">
                <img src="../../img/comentarios.png" class="comment-icon">
              </button>
            </div>

            <div class="comments" id="c_${p.id}" style="display:none;"></div>
          </div>
        </div>
      `;

      list.appendChild(card);
    }

    document.querySelectorAll(".like-btn").forEach((btn) => {
      btn.onclick = async () => {
        try {
          const id = btn.dataset.id;
          const resp = await api("../api/like.php", {
            method: "POST",
            body: JSON.stringify({ post_id: Number(id), action: "toggle" }),
          });
          btn.classList.toggle("liked");
          qs(`#lc_${id}`).textContent = resp.like_count;
        } catch (e) {
          alert(e.message);
        }
      };
    });

    document.querySelectorAll(".show-comments").forEach((btn) => {
      btn.onclick = async () => {
        const id = btn.dataset.id;
        const box = qs("#c_" + id);
        if (box.style.display === "none") {
          await loadComments(id);
          box.style.display = "block";
        } else {
          box.style.display = "none";
        }
      };
    });
  }

  async function loadComments(postId) {
    const box = qs("#c_" + postId);
    if (!box) return;

    const data = await api("../api/comments.php?post_id=" + postId);

    let html = `
      <div class="comment-input">
        <img src="../../img/postar.png" class="comment-avatar">
        <input id="newc_${postId}" placeholder="Escreva um comentário..." />
        <button id="sendc_${postId}" class="btn-send">Enviar</button>
      </div>
    `;

    for (const c of data.comments) {
      const date = new Date(c.created_at.replace(" ", "T"));
      const avatar = resolveAvatar(c.avatar_url);
      html += `
        <div class="comment">
          <img src="${avatar}" style="width:20px;height:20px;border-radius:50%;vertical-align:middle"> 
          <span class="username">${c.name}</span>
          <span class="muted">@${c.username} • ${date.toLocaleString()}</span>
          <div style="white-space:pre-wrap;">${linkifyMentions(c.content)}</div>
        </div>`;
    }

    box.innerHTML = html;

    qs(`#sendc_${postId}`).onclick = async () => {
      const content = qs(`#newc_${postId}`).value.trim();
      if (!content) return;
      try {
        await api("../api/comments.php", {
          method: "POST",
          body: JSON.stringify({ post_id: Number(postId), content }),
        });
        await loadComments(postId);
        await refreshNotifBadge();
      } catch (e) {
        alert(e.message);
      }
    };
  }

  const openPostModal = () => {
    postModal.classList.add("active");
    document.body.classList.add("modal-open");
    setTimeout(() => postContent.focus(), 100);
  };
  const closePostModal = () => {
    postModal.classList.remove("active");
    document.body.classList.remove("modal-open");
  };

  const btnPost = document.getElementById("btn-post");
  if (btnPost) btnPost.addEventListener("click", openPostModal);
  if (closePost) closePost.addEventListener("click", closePostModal);

  window.addEventListener("click", (e) => {
    if (e.target === postModal) closePostModal();
  });

  if (publishBtn) {
    publishBtn.addEventListener("click", async () => {
      const content = postContent.value.trim();
      if (!content) return alert("Escreva algo antes de publicar!");
      try {
        await api("../api/posts.php", {
          method: "POST",
          body: JSON.stringify({ content }),
        });
        postContent.value = "";
        closePostModal();
        await loadPosts();
        await refreshNotifBadge();
      } catch (e) {
        alert(e.message);
      }
    });
  }

  const btnProfile = document.getElementById("btn-profile");
  if (btnProfile)
    btnProfile.addEventListener("click", () => {
      window.location.href = "../../perfil/";
    });

  const btnNotifications = document.getElementById("btn-notifications");
  if (btnNotifications)
    btnNotifications.addEventListener("click", () => openNotifications(true));

  const searchBtn = qs("#search-btn");
  const searchInput = qs("#search");

  if (searchBtn) {
    searchBtn.onclick = () => {
      state.q = (searchInput?.value || "").trim();
      state.page = 1;
      loadPosts();
    };
  }

  if (searchInput) {
    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") searchBtn?.click();
    });
  }

  await renderAuth();
  await loadPosts();

  document.addEventListener("click", (e) => {
    const notifModal = document.getElementById("notif-modal");
    const btnNotifications = document.getElementById("btn-notifications");
    if (!notifModal) return;

    const clickedInside = notifModal.contains(e.target);
    const clickedBtn =
      btnNotifications && btnNotifications.contains(e.target);

    if (!clickedInside && !clickedBtn) {
      notifModal.classList.remove("active");
      document.body.classList.remove("modal-open");
    }
  });
});