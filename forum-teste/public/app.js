let state = { page: 1, per_page: 10, q: '' };

async function api(url, opts = {}){
  const res = await fetch(url, Object.assign({headers:{'Content-Type':'application/json'}}, opts));
  if(!res.ok){
    const err = await res.json().catch(()=>({error:res.statusText}));
    throw new Error(err.error || 'Erro');
  }
  return res.json();
}

const qs = (s, el=document)=>el.querySelector(s);

function sanitize(text){
  return text.replace(/[&<>]/g, s=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[s]));
}

function linkifyMentions(text){
  return sanitize(text).replace(/@([A-Za-z0-9_.]+)/g, '<a href="#" class="mention" data-u="$1">@$1</a>');
}

async function renderAuth(){
  const el = qs('#auth');
  const me = await api('../api/me.php').catch(()=>({user:null}));
  if(me.user){
    el.innerHTML = `
      <div class="row">
        <img src="${me.user.avatar_url || 'https://avatars.githubusercontent.com/u/0?v=4'}" alt="avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
        <span>Olá, <strong>${me.user.name}</strong> (@${me.user.username})</span>
        <button id="btn-profile" class="secondary">Perfil</button>
        <button id="btn-notifs" class="secondary">Notificações</button>
        <span id="notif-badge" class="muted"></span>
        <button class="secondary" id="logout">Sair</button>
      </div>`;
    qs('#new-post-card').style.display = 'block';
    qs('#logout').onclick = async ()=>{
      await api('../api/logout.php', {method:'POST', body:'{}'});
      location.reload();
    };
    qs('#btn-profile').onclick = openProfile;
    qs('#btn-notifs').onclick = openNotifications;
    refreshNotifBadge();
  } else {
    el.innerHTML = `
      <div class="row">
        <input id="name" placeholder="Nome" />
        <input id="username" placeholder="Usuário" />
        <input id="password" placeholder="Senha" type="password" />
        <button id="register">Cadastrar</button>
        <button class="secondary" id="login">Entrar</button>
      </div>`;
    qs('#new-post-card').style.display = 'none';
    qs('#register').onclick = async ()=>{
      const name = qs('#name').value.trim();
      const username = qs('#username').value.trim();
      const password = qs('#password').value;
      try{
        await api('../api/register.php', {method:'POST', body:JSON.stringify({name, username, password})});
        alert('Usuário criado! Agora faça login.');
      }catch(e){ alert(e.message); }
    };
    qs('#login').onclick = async ()=>{
      const username = qs('#username').value.trim();
      const password = qs('#password').value;
      try{
        await api('../api/login.php', {method:'POST', body:JSON.stringify({username, password})});
        location.reload();
      }catch(e){ alert(e.message); }
    };
  }
}

async function refreshNotifBadge(){
  try{
    const data = await api('../api/notifications.php');
    const badge = qs('#notif-badge');
    badge.textContent = data.unread > 0 ? `(${data.unread} novas)` : '(0)';
  }catch(e){}
}

async function openProfile(){
  try{
    const data = await api('../api/profile_get.php');
    const p = data.profile;
    const name = prompt('Nome:', p.name || '');
    if (name === null) return;
    const avatar_url = prompt('URL do avatar (jpg/png):', p.avatar_url || '');
    if (avatar_url === null) return;
    const bio = prompt('Bio:', p.bio || '');
    if (bio === null) return;
    await api('../api/profile_update.php', {method:'POST', body: JSON.stringify({name, avatar_url, bio})});
    await renderAuth();
    await loadPosts();
  }catch(e){ alert(e.message); }
}

function renderNotificationsList(items){
  if (!items || items.length === 0) return '<p class="muted">Sem notificações.</p>';
  return items.map(n=>{
    const kind = n.type === 'mention_post' ? 'mencionou você em um post' : 'mencionou você em um comentário';
    const when = new Date(n.created_at.replace(' ', 'T')).toLocaleString();
    return `<div class="comment">
      <div class="row" style="justify-content:space-between;">
        <div><img src="${n.actor_avatar || 'https://avatars.githubusercontent.com/u/0?v=4'}" style="width:20px;height:20px;border-radius:50%;vertical-align:middle"> <strong>@${n.actor_username}</strong> ${kind}</div>
        <span class="muted">${when}</span>
      </div>
      <div class="row" style="gap:.5rem; margin-top:.25rem;">
        <button class="secondary go-post" data-id="${n.source_post_id}">Ver post</button>
      </div>
    </div>`;
  }).join('');
}

async function openNotifications(){
  try{
    const data = await api('../api/notifications.php');
    const html = renderNotificationsList(data.notifications);
    const tmp = document.createElement('div');
    tmp.className = 'card';
    tmp.innerHTML = `<h3>Notificações</h3>` + html + `<div class="row" style="justify-content:flex-end;margin-top:.5rem;"><button id="mark-all" class="">Marcar todas como lidas</button></div>`;
    const toolbar = qs('#toolbar');
    toolbar.innerHTML = '';
    toolbar.appendChild(tmp);
    tmp.querySelectorAll('.go-post').forEach(b=>{
      b.onclick = ()=>{
        state.page = 1;
        state.q = '';
        loadPosts().then(()=>{
          // simples: apenas recarrega; poderia rolar até o post pelo id via ancora
        });
      };
    });
    tmp.querySelector('#mark-all').onclick = async ()=>{
      await api('../api/notifications.php', {method:'POST', body: JSON.stringify({action:'mark_read_all'})});
      await refreshNotifBadge();
      await openNotifications();
    };
  }catch(e){ alert(e.message); }
}

async function loadPosts(){
  const list = qs('#posts');
  const params = new URLSearchParams({page: state.page, per_page: state.per_page});
  if (state.q) params.set('q', state.q);
  const data = await api('../api/posts.php?' + params.toString());
  list.innerHTML = '';
  if(data.posts.length === 0){
    list.innerHTML = '<p class="muted">Nenhuma postagem por enquanto.</p>';
  } else {
    for (const p of data.posts){
      const card = document.createElement('div');
      card.className = 'post';
      const date = new Date(p.created_at.replace(' ', 'T'));
      const avatar = p.avatar_url || 'https://avatars.githubusercontent.com/u/0?v=4';
      card.innerHTML = `
        <div class="row">
          <img src="${avatar}" alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover">
          <div>
            <div><span class="username">${p.name}</span> <span class="muted">@${p.username} • ${date.toLocaleString()}</span></div>
            <div style="margin:.5rem 0 .5rem; white-space:pre-wrap;">${linkifyMentions(p.content)}</div>
            <div class="actions">
              <button class="like-btn ${p.liked_by_me ? 'liked':''}" data-id="${p.id}">Curtir (${p.like_count})</button>
              <button class="secondary show-comments" data-id="${p.id}">Comentários (${p.comment_count})</button>
            </div>
            <div class="comments" id="c_${p.id}" style="display:none; margin-top:.5rem;"></div>
          </div>
        </div>
      `;
      list.appendChild(card);
    }
  }

  // pagination controls
  const totalPages = Math.max(1, Math.ceil(data.total / data.per_page));
  qs('#page-info').textContent = `Página ${data.page} de ${totalPages} — ${data.total} resultados`;
  qs('#prev').disabled = data.page <= 1;
  qs('#next').disabled = data.page >= totalPages;

  document.querySelectorAll('.like-btn').forEach(btn=>{
    btn.onclick = async ()=>{
      const id = btn.dataset.id;
      try{
        const resp = await api('../api/like.php', {method:'POST', body:JSON.stringify({post_id:Number(id), action:'toggle'})});
        btn.textContent = `Curtir (${resp.like_count})`;
        btn.classList.toggle('liked');
      }catch(e){ alert(e.message); }
    };
  });

  document.querySelectorAll('.show-comments').forEach(btn=>{
    btn.onclick = async ()=>{
      const id = btn.dataset.id;
      const box = qs('#c_'+id);
      if (box.style.display === 'none'){
        await loadComments(id);
        box.style.display = 'block';
      } else {
        box.style.display = 'none';
      }
    };
  });

  document.querySelectorAll('.mention').forEach(a=>{
    a.onclick = (e)=>{
      e.preventDefault();
      state.q = 'from:@' + a.dataset.u;
      state.page = 1;
      loadPosts();
    };
  });
}

async function loadComments(postId){
  const box = qs('#c_'+postId);
  const data = await api('../api/comments.php?post_id='+postId);
  let html = '';
  html += `
    <div class="row" style="margin:.5rem 0;">
      <input id="newc_${postId}" placeholder="Escreva um comentário com @menções..." style="flex:1" />
      <button id="sendc_${postId}">Enviar</button>
    </div>
  `;
  for (const c of data.comments){
    const date = new Date(c.created_at.replace(' ', 'T'));
    const avatar = c.avatar_url || 'https://avatars.githubusercontent.com/u/0?v=4';
    html += `<div class="comment">
      <img src="${avatar}" style="width:20px;height:20px;border-radius:50%;vertical-align:middle"> 
      <span class="username">${c.name}</span> <span class="muted">@${c.username} • ${date.toLocaleString()}</span>
      <div style="white-space:pre-wrap;">${linkifyMentions(c.content)}</div>
    </div>`;
  }
  box.innerHTML = html;
  qs('#sendc_'+postId).onclick = async ()=>{
    const content = qs('#newc_'+postId).value.trim();
    if (!content) return;
    try{
      await api('../api/comments.php', {method:'POST', body:JSON.stringify({post_id:Number(postId), content})});
      await loadComments(postId);
      await refreshNotifBadge();
    }catch(e){ alert(e.message); }
  };
}

async function init(){
  await renderAuth();
  await loadPosts();

  const btn = qs('#btn-post');
  if (btn){
    btn.onclick = async ()=>{
      const content = qs('#post-content').value.trim();
      if (!content) return;
      try{
        await api('../api/posts.php', {method:'POST', body:JSON.stringify({content})});
        qs('#post-content').value = '';
        await loadPosts();
        await refreshNotifBadge();
      }catch(e){ alert(e.message); }
    }
  }

  // search
  qs('#btn-search').onclick = ()=>{
    state.q = qs('#search').value.trim();
    state.page = 1;
    loadPosts();
  };
  qs('#search').addEventListener('keypress', (e)=>{
    if (e.key === 'Enter') { qs('#btn-search').click(); }
  });

  // pagination buttons
  qs('#prev').onclick = ()=>{ if (state.page>1){ state.page--; loadPosts(); } };
  qs('#next').onclick = ()=>{ state.page++; loadPosts(); };
}
init();
