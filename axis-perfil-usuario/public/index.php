<?php
$user_id = 1; // ajuste para trocar o usuário carregado

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Perfil de usuário · AXIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/styles.css"/>
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <nav class="topbar">
    <div class="logo">
      <span class="globe">🌐</span>
      <span class="title">Perfil de usuário</span>
    </div>
    <ul class="menu">
      <li>Início</li>
      <li>Matérias</li>
      <li>Comunidade</li>
      <li class="active">Perfil</li>
    </ul>
    <div class="right-actions"><button class="burger" aria-label="menu">☰</button><a href="logout.php" id="btnLogout" class="chip alt" style="margin-left:12px">sair</a></div>
  </nav>

  <main class="container">
    <section class="card user-card">
      <div class="avatar-wrap">
        <img id="avatar" src="../backend/uploads/demo.jpg" alt="Foto de perfil" onerror="this.src='https://i.pravatar.cc/240?img=5'">
        <button id="btnUpload" class="chip">editar foto</button>
        <input type="file" id="fileAvatar" accept="image/*" hidden>
      </div>
      <div class="user-info">
        <h2 id="name">—</h2>
        <div class="handle">Usuário: <span id="username">@ —</span></div>
        <div class="badges">
          <span class="badge"><span>👤</span> aluno</span>
          <span class="badge"><span>🧠</span> <span id="points">0</span> pts</span>
        </div>
        <button id="btnEdit" class="chip">editar informações</button>
      </div>
    </section>

    <section class="grid">
      <div class="card" id="addressCard">
        <div class="card-head">
          <h3>endereço:</h3>
          <button class="icon-btn" id="toggleEdit">✎</button>
        </div>
        <div class="form" id="formAddress">
          <label>CEP - <input id="cep" placeholder="00000-000"></label>
          <label>Rua <input id="street" placeholder="Rua Exemplo"></label>
          <label>Número <input id="number" placeholder="123"></label>
          <label>Cidade/Bairro <input id="city" placeholder="Cidade"></label>
          <button class="primary" id="saveAddress">salvar</button>
        </div>
      </div>

      <div class="card" id="passwordCard">
        <h3>senha:</h3>
        <div class="form">
          <label>Atual <input type="password" id="pwdCurrent" placeholder="********"></label>
          <label>Nova <input type="password" id="pwdNew" placeholder="********"></label>
          <button class="primary" id="changePwd">trocar senha</button>
          <div id="pwdMsg" class="msg"></div>
        </div>
      </div>

      <div class="card wide" id="progressCard">
        <h3>Seu progresso:</h3>
        <p class="sub">Aulas assistidas e questões respondidas.</p>
        <canvas id="barLessons" height="160"></canvas>
        <canvas id="barExercises" height="160" class="mt"></canvas>
        <button id="moreStats" class="chip alt">veja mais estatísticas</button>
      </div>
    </section>
  </main>

  <template id="editModalTpl">
    <dialog class="modal">
      <form method="dialog">
        <h3>Editar informações</h3>
        <label>Nome <input id="m_name"></label>
        <label>Usuário (@) <input id="m_username"></label>
        <div class="actions">
          <button value="cancel">cancelar</button>
          <button id="m_save" value="ok" class="primary">salvar</button>
        </div>
      </form>
    </dialog>
  </template>

  <script>
    const USER_ID = <?php echo (int)$user_id; ?>;
    const API = '../backend/api';
  </script>
  <script src="assets/app.js"></script>
</body>
</html>
