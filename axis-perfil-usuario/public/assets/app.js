const weekdayLabels = ['seg','ter','qua','qui','sex','sab','dom'];

const ValueLabels = {
  id:'ValueLabels',
  afterDatasetsDraw(chart,args,pluginOptions){
    const {ctx} = chart;
    ctx.save();
    chart.getDatasetMeta(0).data.forEach((bar, i)=>{
      const val = chart.data.datasets[0].data[i];
      ctx.font = '600 12px Inter, system-ui, sans-serif';
      ctx.fillStyle = '#e5e7eb';
      ctx.textAlign = 'center';
      ctx.fillText(val, bar.x, bar.y - 8);
    });
    ctx.restore();
  }
};

function $(sel){ return document.querySelector(sel); }
function api(path){ return `${API}/${path}`; }
function fmt(n){ return Intl.NumberFormat('pt-BR').format(n); }

async function loadUser(){
  const r = await fetch(api(`get_user.php?user_id=${USER_ID}`));
  const {user, profile} = await r.json();
  $('#name').textContent = user.name;
  $('#username').textContent = '@'+user.username;
  $('#points').textContent = fmt(user.points || 0);
  if (user.avatar_path) $('#avatar').src = '../'+user.avatar_path;

  $('#cep').value = profile.cep || '';
  $('#street').value = profile.street || '';
  $('#number').value = profile.number || '';
  $('#city').value = profile.city || '';

  // modal
  const tpl = document.getElementById('editModalTpl');
  const dialog = tpl.content.firstElementChild.cloneNode(true);
  document.body.appendChild(dialog);
  dialog.querySelector('#m_name').value = user.name;
  dialog.querySelector('#m_username').value = user.username;

  document.getElementById('btnEdit').onclick = ()=> dialog.showModal();
  dialog.querySelector('#m_save').onclick = async (ev)=>{
    ev.preventDefault();
    await fetch(api('update_profile.php'), {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        user_id: USER_ID,
        name: dialog.querySelector('#m_name').value,
        username: dialog.querySelector('#m_username').value,
        cep: $('#cep').value, street: $('#street').value, number: $('#number').value, city: $('#city').value
      })
    });
    dialog.close();
    loadUser();
  };
}

async function saveAddress(){
  await fetch(api('update_profile.php'), {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({
      user_id: USER_ID,
      name: $('#name').textContent,
      username: $('#username').textContent.replace('@',''),
      cep: $('#cep').value, street: $('#street').value, number: $('#number').value, city: $('#city').value
    })
  });
}

async function changePwd(){
  $('#pwdMsg').textContent = '';
  const current = $('#pwdCurrent').value;
  const n = $('#pwdNew').value;
  const r = await fetch(api('change_password.php'), {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({user_id: USER_ID, current, new:n})
  });
  const data = await r.json();
  $('#pwdMsg').textContent = data.ok ? 'Senha alterada com sucesso!' : (data.error || 'Erro');
}

function buildBars(lessons, exercises){
  const ctxB = document.getElementById('barLessons').getContext('2d');
  new Chart(ctxB,{
    type:'bar',
    data:{
      labels: weekdayLabels,
      datasets:[{
        label:'Aulas',
        data: weekdayLabels.map((_,i)=> lessons[i+1] ?? 0),
        backgroundColor:'#FF79B0',
        borderRadius:10, maxBarThickness:36
      }]
    },
    options:{
      scales:{
        x:{ticks:{color:'#E5E7EB', font:{weight:'600'}}},
        y:{beginAtZero:true, ticks:{color:'#E5E7EB', stepSize:1}}
      },
      plugins:{
        legend:{display:false},
        tooltip:{callbacks:{label: ctx => ` ${ctx.raw} aulas`}}
      }
    },
    plugins:[ValueLabels]
  });

  const ctxC = document.getElementById('barExercises').getContext('2d');
  new Chart(ctxC,{
    type:'bar',
    data:{
      labels: weekdayLabels,
      datasets:[{
        label:'Questões',
        data: weekdayLabels.map((_,i)=> exercises[i+1] ?? 0),
        backgroundColor:'#ffd447',
        borderRadius:10, maxBarThickness:36
      }]
    },
    options:{
      scales:{
        x:{ticks:{color:'#E5E7EB', font:{weight:'600'}}},
        y:{beginAtZero:true, ticks:{color:'#E5E7EB', stepSize:1}}
      },
      plugins:{
        legend:{display:false},
        tooltip:{callbacks:{label: ctx => ` ${ctx.raw} questões`}}
      }
    }
  });
}

async function loadMetrics(){
  const r = await fetch(api(`metrics.php?user_id=${USER_ID}`));
  const data = await r.json();
  buildBars(data.lessons, data.exercises);
}

function bindUI(){
  $('#toggleEdit').onclick = ()=> $('#formAddress').classList.toggle('editing');
  $('#saveAddress').onclick = async ()=> { await saveAddress(); alert('Endereço salvo!'); };
  $('#changePwd').onclick = changePwd;
  $('#btnUpload').onclick = ()=> $('#fileAvatar').click();
  $('#fileAvatar').onchange = async (e)=>{
    const fd = new FormData();
    fd.append('user_id', USER_ID);
    fd.append('avatar', e.target.files[0]);
    const r = await fetch(api('upload_avatar.php'), {method:'POST', body:fd});
    const data = await r.json();
    if (data.ok) $('#avatar').src = '../'+data.avatar_path;
  };
}

(async function(){
  bindUI();
  await loadUser();
  await loadMetrics();
})();