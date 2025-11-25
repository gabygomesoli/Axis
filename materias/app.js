document.addEventListener("DOMContentLoaded", function () {
  const transitionType = "fade";
  const navbarToggle = document.querySelector(".navbar-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");
  const menuOverlay = document.querySelector(".menu-overlay");
  const cardsContainer = document.querySelector(".cards-container");

  if (navbarToggle && mobileMenu && menuOverlay) {
    navbarToggle.addEventListener("click", () => {
      navbarToggle.classList.toggle("active");
      mobileMenu.classList.toggle("active");
      menuOverlay.classList.toggle("active");
      document.body.style.overflow = mobileMenu.classList.contains("active") ? "hidden" : "";
    });

    menuOverlay.addEventListener("click", () => {
      navbarToggle.classList.remove("active");
      mobileMenu.classList.remove("active");
      menuOverlay.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  const area = document.body.classList.contains("humanas")
    ? "humanas"
    : document.body.classList.contains("matematica")
    ? "matematica"
    : document.body.classList.contains("linguagens")
    ? "linguagens"
    : "biologicas";

  const root = document.documentElement;
  const temas = {
    biologicas: { cor: "#67D040", hover: "rgba(103, 208, 64, 0.4)" },
    humanas: { cor: "#EB6739", hover: "rgba(235, 103, 57, 0.4)" },
    matematica: { cor: "#13B3FF", hover: "rgba(19, 179, 255, 0.4)" },
    linguagens: { cor: "#9D77E0", hover: "rgba(157, 119, 224, 0.4)" },
  };

  root.style.setProperty("--area-color", temas[area].cor);
  root.style.setProperty("--area-hover", temas[area].hover);

  document.querySelectorAll(".mini-card").forEach((card) => {
    if (card.classList.contains("ativo")) {
      card.style.borderColor = temas[area].cor;
      card.style.color = temas[area].cor;
      card.style.boxShadow = `0 0 15px ${temas[area].hover}`;
    } else {
      card.style.borderColor = "#0493D7";
      card.style.color = "#65BEE9";
      card.style.boxShadow = "none";
    }
  });

  if (cardsContainer) {
    const abas = document.querySelectorAll(".abas-materias button");
    abas.forEach((aba) => {
      aba.addEventListener("click", (e) => {
        e.preventDefault();
        const url = aba.getAttribute("onclick")
          ? aba.getAttribute("onclick").match(/'(.*?)'/)[1]
          : aba.dataset.href || "#";

        if (transitionType === "fade") {
          cardsContainer.style.transition = "opacity 0.4s ease";
          cardsContainer.style.opacity = "0";
          setTimeout(() => (window.location.href = url), 400);
        } else if (transitionType === "slide") {
          cardsContainer.style.transition = "transform 0.4s ease, opacity 0.4s ease";
          cardsContainer.style.transform = "translateX(-40px)";
          cardsContainer.style.opacity = "0";
          setTimeout(() => (window.location.href = url), 400);
        } else {
          window.location.href = url;
        }
      });
    });

    cardsContainer.style.opacity = "0";
    cardsContainer.style.transition = "all 0.4s ease";
    setTimeout(() => {
      cardsContainer.style.opacity = "1";
      cardsContainer.style.transform = "translateX(0)";
    }, 100);
  }

  const carousel = document.querySelector(".cards-carousel");
  const arrowLeft = document.querySelector(".arrow.left");
  const arrowRight = document.querySelector(".arrow.right");

  if (carousel && arrowLeft && arrowRight) {
    arrowLeft.addEventListener("click", () => {
      carousel.scrollBy({
        left: -350,
        behavior: "smooth",
      });
    });

    arrowRight.addEventListener("click", () => {
      carousel.scrollBy({
        left: 350,
        behavior: "smooth",
      });
    });
  }
});