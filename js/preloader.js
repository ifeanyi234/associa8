function hidePreloader() {
  var preloader = document.getElementById("preloader");
  if (preloader) preloader.classList.add("is-hidden");
}

window.addEventListener("load", function () {
  setTimeout(hidePreloader, 900);
});

setTimeout(hidePreloader, 6000);
