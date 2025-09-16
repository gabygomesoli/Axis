<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Splash</title>
  <link rel="stylesheet" href="style.css">
  <style>

  
    .splash img {
      width: 270px;
      margin-bottom: 20px;
      animation: pulse 2s infinite;
    }

    body, html {
      height: 100%;
      margin: 0;
     background-color: #004681; /* Coloque a cor que quiser */
     display: flex;
     justify-content: center;
     align-items: center;
   }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
  </style>
  <script>
    // Tempo da splash (3 segundos)
    setTimeout(function(){
      window.location.href = "index.php";
    }, 3000);
  </script>
</head>
<body>


  <div class="splash">
    <img src="img/logo.png" alt="Logo"> 
  </div>
</body>
</html>
