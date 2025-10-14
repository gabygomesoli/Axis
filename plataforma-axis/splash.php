<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Splash</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body, html {height:100%;margin:0;display:flex;justify-content:center;align-items:center;background:#004681;}
    .splash img {width:270px;animation:pulse 2s infinite;}
    @keyframes pulse {0%{transform:scale(1);}50%{transform:scale(1.1);}100%{transform:scale(1);}}
  </style>
  <script>
    setTimeout(()=>{ window.location.href = "index.php"; }, 3000);
  </script>
</head>
<body>
  <div class="splash">
    <img src="img/logo.png" alt="Logo">
  </div>
</body>
</html>
