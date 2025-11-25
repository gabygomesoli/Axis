const $  = sel => document.querySelector(sel);
const $$ = sel => document.querySelectorAll(sel);

const weekdayLabels = ['seg','ter','qua','qui','sex','sab','dom'];

function resolveAvatar(path) {
  if (!path) {
    return '../img/perfilpadrao.png';
  }
  if (/^https?:\/\//i.test(path)) {
    return path;
  }
  return '../' + path.replace(/^\/+/, '');
}

async function api(path, options = {}) {
  const res = await fetch(`${PROFILE_API_BASE}/${path}`, options);
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const msg = data.error || 'Erro na comunicação com o servidor.';
    throw new Error(msg);
  }
  return data;
}

function setupMenu() {
  const burger  = $('#navbarToggle');
  const menu    = $('#mobileMenu');
  const overlay = $('#menuOverlay');

  if (!burger || !menu || !overlay) return;

  const toggle = () => {
    const isActive = menu.classList.toggle('active');
    overlay.classList.toggle('active');
    burger.classList.toggle('active');
    document.body.style.overflow = isActive ? 'hidden' : '';
  };

  burger.addEventListener('click', toggle);
  overlay.addEventListener('click', toggle);
  $$('.mobile-menu a').forEach(a => a.addEventListener('click', toggle));
}

function setupNavbarScroll() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  const onScroll = () => {
    if (window.scrollY > 10) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', onScroll);
  onScroll();
}

async function loadProfile() {
  const data = await api('get_profile.php');
  const u = data.user;

  $('#profile-name').textContent = u.nome_completo;
  $('#profile-username').textContent = '@' + u.nome_usuario;
  $('#profile-email').textContent = u.email;
  $('#profile-role').innerHTML = `<span class="chip-icon">🎓</span> ${u.tipo}`;
  $('#profile-points').textContent = u.pontos ?? 0;
  $('#profile-created').textContent = u.criado_em_br || '--/--/----';

  $('#avatar-img').src = resolveAvatar(u.foto_perfil);

  const cepInput    = $('#field-cep');
  const streetInput = $('#field-street');
  const cityInput   = $('#field-city');

  if (cepInput)    cepInput.value    = u.cep_formatado || '';
  if (streetInput) streetInput.value = u.logradouro || '';
  if (cityInput)   cityInput.value   = u.cidade || '';
}


function setupEditProfile() {
  const modal  = $('#edit-modal');
  const btnOpen = $('#btn-open-edit');
  const form   = $('#form-edit-profile');
  const msg    = $('#edit-msg');

  if (btnOpen && modal) {
    btnOpen.addEventListener('click', () => {
      msg.textContent = '';
      modal.showModal();
    });
  }

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      msg.textContent = '';

      const payload = {
        nome_completo: $('#edit-name').value.trim(),
        nome_usuario:  $('#edit-username').value.trim(),
        tipo:          $('#edit-type').value
      };

      try {
        await api('update_profile.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        msg.textContent = 'Informações atualizadas com sucesso!';
        await loadProfile();
        setTimeout(() => modal.close(), 800);
      } catch (err) {
        msg.textContent = err.message;
      }
    });
  }
}

function setupAddressForm() {
  const btnSave = $('#btn-save-address');
  const msg     = $('#address-msg');

  if (!btnSave) return;

  btnSave.addEventListener('click', async () => {
    msg.textContent = '';

    const payload = {
      cep:    $('#field-cep').value.trim(),
      street: $('#field-street').value.trim(),
      city:   $('#field-city').value.trim()
    };

    try {
      await api('update_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      msg.textContent = 'Endereço salvo!';
    } catch (err) {
      msg.textContent = err.message;
    }
  });
}

function setupPasswordForm() {
  const btn = $('#btn-change-password');
  const msg = $('#password-msg');

  if (!btn) return;

  btn.addEventListener('click', async () => {
    msg.textContent = '';

    const current = $('#field-current-pass').value;
    const nova    = $('#field-new-pass').value;
    const conf    = $('#field-new-pass-confirm').value;

    if (!current || !nova || !conf) {
      msg.textContent = 'Preencha todos os campos.';
      return;
    }
    if (nova !== conf) {
      msg.textContent = 'A confirmação não coincide.';
      return;
    }

    try {
      await api('change_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          current_password: current,
          new_password: nova
        })
      });
      msg.textContent = 'Senha alterada com sucesso!';
      $('#field-current-pass').value = '';
      $('#field-new-pass').value = '';
      $('#field-new-pass-confirm').value = '';
    } catch (err) {
      msg.textContent = err.message;
    }
  });
}

function setupAvatarUpload() {
  const btn   = $('#btn-change-avatar');
  const input = $('#avatar-file');
  const img   = $('#avatar-img');

  if (!btn || !input || !img) return;

  btn.addEventListener('click', () => input.click());

  input.addEventListener('change', async () => {
    if (!input.files[0]) return;

    const formData = new FormData();
    formData.append('avatar', input.files[0]);

    try {
      const data = await api('upload_avatar.php', {
        method: 'POST',
        body: formData
      });
      img.src = resolveAvatar(data.foto_perfil);
    } catch (err) {
      alert(err.message);
    }
  });
}

async function loadMetrics() {
  let lessons   = {1:5,2:3,3:4,4:6,5:7,6:2,7:5};
  let questions = {1:4,2:6,3:3,4:5,5:8,6:4,7:3};

  const canvasL = document.getElementById('chart-lessons');
  const canvasQ = document.getElementById('chart-questions');
  if (!canvasL || !canvasQ) return;

  const ctxL = canvasL.getContext('2d');
  const ctxQ = canvasQ.getContext('2d');

  new Chart(ctxL, {
    type: 'bar',
    data: {
      labels: weekdayLabels,
      datasets: [{
        label: 'Aulas',
        data: weekdayLabels.map((_, i) => lessons[i + 1] || 0),
        backgroundColor: '#FF79B0',
        borderRadius: 12
      }]
    },
    options: {
      plugins: { legend: { display:false } },
      scales: {
        x: { ticks: { color:'#E5E7EB' } },
        y: { beginAtZero:true, ticks:{ stepSize:1, color:'#E5E7EB' } }
      }
    }
  });

  new Chart(ctxQ, {
    type: 'bar',
    data: {
      labels: weekdayLabels,
      datasets: [{
        label: 'Questões',
        data: weekdayLabels.map((_, i) => questions[i + 1] || 0),
        backgroundColor: '#FFD447',
        borderRadius: 12
      }]
    },
    options: {
      plugins: { legend: { display:false } },
      scales: {
        x: { ticks: { color:'#E5E7EB' } },
        y: { beginAtZero:true, ticks:{ stepSize:1, color:'#E5E7EB' } }
      }
    }
  });
}

window.addEventListener('DOMContentLoaded', async () => {
  setupMenu();
  setupNavbarScroll();
  setupEditProfile();
  setupAddressForm();
  setupPasswordForm();
  setupAvatarUpload();
  await loadProfile();
  await loadMetrics();
});