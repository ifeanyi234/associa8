document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("adminSidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const toggle = document.getElementById("sidebarToggle");
  const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

  if (!sidebar) return;

  const closeSidebar = () => {
    sidebar.classList.remove("open");
    overlay?.classList.remove("active");
  };

  const openSidebar = () => {
    sidebar.classList.add("open");
    overlay?.classList.add("active");
  };

  toggle?.addEventListener("click", () => {
    sidebar.classList.contains("open") ? closeSidebar() : openSidebar();
  });
  overlay?.addEventListener("click", closeSidebar);

  dropdownItems.forEach((item) => {
    const link = item.querySelector(".sidebar-link");
    const submenu = item.querySelector(".sidebar-submenu");
    if (!link || !submenu) return;

    link.addEventListener("click", (event) => {
      event.preventDefault();
      const isOpen = item.classList.contains("open");

      dropdownItems.forEach((otherItem) => {
        if (otherItem === item) return;
        otherItem.classList.remove("open");
        const otherSubmenu = otherItem.querySelector(".sidebar-submenu");
        if (otherSubmenu) otherSubmenu.style.maxHeight = null;
      });

      item.classList.toggle("open", !isOpen);
      submenu.style.maxHeight = isOpen ? null : `${submenu.scrollHeight}px`;
    });
  });

  sidebar.querySelectorAll("a").forEach((link) => {
    const parentDropdown = link.closest(".dropdown");
    const dropdownToggle = parentDropdown?.querySelector(".sidebar-link");
    if (!parentDropdown || link !== dropdownToggle) {
      link.addEventListener("click", closeSidebar);
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeSidebar();
      dropdownItems.forEach((item) => item.classList.remove("open"));
    }
  });
});
