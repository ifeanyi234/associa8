(function () {
  var toggle = document.getElementById("menuToggle");
  var menu = document.getElementById("mobileMenu");
  var overlay = document.getElementById("mobileMenuOverlay");
  var body = document.body;

  if (!toggle || !menu || !overlay) return;

  function openMenu() {
    toggle.classList.add("active");
    menu.classList.add("active");
    overlay.classList.add("active");
    toggle.setAttribute("aria-expanded", "true");
    body.classList.add("mobile-menu-open");
  }

  function closeMenu() {
    toggle.classList.remove("active");
    menu.classList.remove("active");
    overlay.classList.remove("active");
    toggle.setAttribute("aria-expanded", "false");
    body.classList.remove("mobile-menu-open");
  }

  toggle.addEventListener("click", function () {
    if (menu.classList.contains("active")) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  overlay.addEventListener("click", closeMenu);

  // Close when a link inside the panel is clicked
  menu.querySelectorAll("a").forEach(function (link) {
    link.addEventListener("click", closeMenu);
  });

  // Close on resize back to desktop
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) closeMenu();
  });
})();
