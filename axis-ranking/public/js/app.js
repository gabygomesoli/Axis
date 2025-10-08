const API = '../backend/api.php';
const CenterText = {
  id:'centerText',
  afterDraw(chart, _args, opts){
    const {ctx, chartArea:{width,height}} = chart;
    ctx.save();
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillStyle='#fff'; ctx.font='700 22px Inter, system-ui, sans-serif';
    ctx.fillText(opts.main||'', width/2, height/2-4);
    ctx.fillStyle='#d1d5db'; ctx.font='600 12px Inter, system-ui, sans-serif';
    if(opts.sub)  ctx.fillText(opts.sub,  width/2, height/2+14);
    ctx.restore();
  }
};
async function fetchAll(){
  const res = await fetch(`${API}?action=all&user_id=4`);
  const json = await res.json();
  if(!json.ok) throw new Error(json.error||'Erro na API');
  return json.data;
}
function donut(el, color, current, target, label){
  const ctx = el.getContext('2d');
  return new Chart(ctx,{
    type:'doughnut',
    data:{labels:['Atual','Restante'],
      datasets:[{data:[current, Math.max(0,target-current)],
      backgroundColor:[color,'rgba(255,255,255,0.08)'], borderWidth:0, cutout:'72%'}]},
    options:{plugins:{legend:{display:false}, tooltip:{enabled:false},
      centerText:{main:`${current} de ${target}`, sub:label}}},
    plugins:[CenterText]
  });
}
function renderLeaderboard(list, currentUserId){
  const wrap = document.querySelector('#leaderboard'); wrap.innerHTML='';
  list.forEach((u, i)=>{
    const me = u.id==currentUserId ? ' (Você)' : '';
    const div = document.createElement('div'); div.className='entry';
    div.innerHTML = `
      <div class="rank">${String(i+1).padStart(2,'0')}</div>
      <div class="name">${u.name}${me}</div>
      <div class="trend ${u.trend>0?'up':u.trend<0?'down':'eq'}">${u.trend>0?'↑':u.trend<0?'↓':'→'} ${u.score}</div>`;
    wrap.appendChild(div);
  });
}
function renderGoals(goals){
  const wrap = document.querySelector('#goals'); wrap.innerHTML='';
  goals.forEach(g=>{
    const el = document.createElement('div'); el.className='goal';
    el.innerHTML = `<canvas></canvas><small>${g.title}</small>`; wrap.appendChild(el);
    donut(el.querySelector('canvas'), g.color, Number(g.current_value), Number(g.target_value), g.title);
  });
}
function renderBadges(badges){
  const row = document.querySelector('#badgeRow'); row.innerHTML='';
  badges.forEach(b=>{
    const div = document.createElement('div');
    div.className = `badge ${b.earned_at ? 'earned' : ''}`;
    div.title = b.title + (b.earned_at ? ' • conquistada' : ' • bloqueada');
    div.innerHTML = `<span>${b.icon}</span>`; row.appendChild(div);
  });
}
async function init(){
  const { user, leaderboard, goals, badges } = await fetchAll();
  renderLeaderboard(leaderboard, user.id); renderGoals(goals); renderBadges(badges);
}
document.addEventListener('DOMContentLoaded', init);
