document.addEventListener("DOMContentLoaded", () => {
  const navbarToggle = document.querySelector(".navbar-toggle");
  const mobileMenu   = document.querySelector(".mobile-menu");
  const menuOverlay  = document.querySelector(".menu-overlay");

  if (!navbarToggle || !mobileMenu || !menuOverlay) return;

  const toggleMenu = (open = null) => {
    const willOpen = open === null ? !mobileMenu.classList.contains("active") : open;

    navbarToggle.classList.toggle("active", willOpen);
    mobileMenu.classList.toggle("active", willOpen);
    menuOverlay.classList.toggle("active", willOpen);

    document.body.style.overflow = willOpen ? "hidden" : "";
  };

  navbarToggle.addEventListener("click", () => toggleMenu());

  menuOverlay.addEventListener("click", () => toggleMenu(false));

  document.querySelectorAll(".mobile-menu a").forEach(a => {
    a.addEventListener("click", () => toggleMenu(false));
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && mobileMenu.classList.contains("active")) {
      toggleMenu(false);
    }
  });

  const currentPage = document.body.dataset.page || "";
  document.querySelectorAll(".navopcoes a, .mobile-menu a").forEach(link => {
    const txt = (link.textContent || "").trim().toLowerCase();
    if (txt === currentPage.trim().toLowerCase()) {
      link.classList.add("active");
    }
  });

  window.Comecar = function() {
    window.location.href = "../materias/materias.php";
  }

  window.Progresso = function () {
    window.location.href = "../dashboard/public/";
  }
});