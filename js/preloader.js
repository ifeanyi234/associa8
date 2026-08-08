// Hide the preloader once everything (images, fonts, etc.) has
// finished loading. Swap `window.addEventListener('load', ...)`
// for `DOMContentLoaded` if you'd rather hide it as soon as the
// HTML/CSS is ready, without waiting on images.
function hidePreloader() {
  var preloader = document.getElementById("preloader");
  if (preloader) preloader.classList.add("is-hidden");
}

window.addEventListener("load", function () {
  // small artificial delay so the animation is visible even on
  // fast connections/local dev — remove this if you don't want it
  setTimeout(hidePreloader, 900);
});

// Safety net: force-hide after 6s no matter what, so a slow or
// broken resource (e.g. the Chart.js CDN) can never leave the
// dashboard permanently stuck behind the preloader.
setTimeout(hidePreloader, 6000);
