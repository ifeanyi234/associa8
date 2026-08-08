document.addEventListener("DOMContentLoaded", () => {
  const navbarWrapper = document.getElementById("navbarWrapper");

  window.addEventListener("scroll", () => {
    // Wait until the user scrolls past 250px to trigger the fixed header
    if (window.scrollY > 250) {
      navbarWrapper.classList.add("scrolled");
    } else {
      navbarWrapper.classList.remove("scrolled");
    }
  });
});
