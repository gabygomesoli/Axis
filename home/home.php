<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$nomeUsuario = $_SESSION['usuario']['nome'];

$paginaAtual = "Início";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $paginaAtual; ?></title>
    <link rel="icon" type="image/png" href="../img/imgnavbar/globoaxis.png">
    <link rel="stylesheet" href="style.css">
</head>

<body data-page="<?php echo htmlspecialchars($paginaAtual, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="background-image"></div>

    <nav class="navbar">
        <div class="logo-section">
            <img src="../img/imgnavbar/globoaxis.png" alt="Logo do site" class="logo">
            <a href="#" class="page-title"><?php echo $paginaAtual; ?></a>
        </div>

        <div class="nav-links">
            <div class="navopcoes">
                <a href="#" class="active">Início</a>
                <a href="../materias/materias.php">Matérias</a>
                <a href="../comunidade/public/">Comunidade</a>
                <a href="../perfil/index.php">Perfil</a>
            </div>
        </div>

        <button class="navbar-toggle" aria-label="Abrir menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </nav>

    <div class="mobile-menu">
        <a href="#" class="active">Início</a>
        <a href="../materias/materias.php">Matérias</a>
        <a href="../forum-teste/public/index.html">Comunidade</a>
        <a href="../perfil/index.php">Perfil</a>
        <div class="menu-divider"></div>
        <a href="../ranking/public/">Ranking</a>
        <a href="../dashboard/public/">Estatísticas</a>
        <a href="#">Redação</a>
        <a href="../corretor/">Corretor por IA</a>
        <a href="../simulado/">Simulados</a>
        <a href="../cronograma/">Cronogramas</a>
        <a href="../autenticar/logout.php">Sair</a>
    </div>
    
    <div class="menu-overlay"></div>
    <main class="main-content">
        <h1 class="greeting">Olá, <?php echo htmlspecialchars($nomeUsuario);?>!</h1>
        <p class="description">Domine o vestibular com a Axis - a plataforma gratuita que oferece materiais completos e recursos interativos para sua preparação no ENEM.</p>
        <div class="buttons">
            <button onclick="Comecar()" class="btn primary">Comece agora!</button>
            <button onclick="Progresso()" class="btn secondary">Ver Meu Progresso</button>
        </div>

        <div class="axis-image">
            <img src="../img/logomenor.png" alt="axis">
        </div>
    </main>

    <div class="mascote-container">
        <div class="mascote-wrapper">
            <?php
            $mascotes = ['mascote.png', 'mascote.gif', 'm_estrela.gif'];

            $mascote_aleatorio = $mascotes[array_rand($mascotes)];

            $caminho_mascote = '../img/' . $mascote_aleatorio;
            ?>
            <img src="<?php echo $caminho_mascote; ?>" alt="Mascote Axis" class="mascote-interativo" id="mascoteInterativo">
            <div class="mascote-glow"></div>
            <div class="mascote-particles" id="mascoteParticles"></div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mascote = document.querySelector("#mascoteInterativo");
            const mascoteWrapper = document.querySelector(".mascote-wrapper");
            const particlesContainer = document.querySelector("#mascoteParticles");

            if (mascote && mascoteWrapper) {
                let isAnimating = false;

                function createParticles() {
                    if (!particlesContainer) return;

                    particlesContainer.innerHTML = '';
                    for (let i = 0; i < 8; i++) {
                        const particle = document.createElement('div');
                        particle.className = 'particle';
                        particle.style.left = Math.random() * 100 + '%';
                        particle.style.top = Math.random() * 100 + '%';
                        particle.style.animationDelay = Math.random() * 2 + 's';
                        particle.style.animationDuration = (2 + Math.random() * 2) + 's';
                        particlesContainer.appendChild(particle);
                    }
                }

                createParticles();

                mascoteWrapper.addEventListener("click", function(e) {
                    if (!isAnimating) {
                        isAnimating = true;

                        this.style.animation = 'none';
                        this.style.transform = 'scale(0.9) rotate(-5deg)';

                        createParticles();

                        setTimeout(() => {
                            this.style.transform = 'scale(1.1) rotate(5deg)';
                            setTimeout(() => {
                                this.style.animation = '';
                                this.style.transform = '';
                                isAnimating = false;
                            }, 200);
                        }, 150);
                    }
                });

                mascoteWrapper.addEventListener("mousemove", function(e) {
                    if (isAnimating) return;

                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const rotateX = (y - centerY) / 25;
                    const rotateY = (centerX - x) / 25;

                    this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
                });

                mascoteWrapper.addEventListener("mouseleave", function() {
                    if (!isAnimating) {
                        this.style.transform = '';
                    }
                });
            }
        });
    </script>
    <script src="script.js"></script>
</body>

</html>