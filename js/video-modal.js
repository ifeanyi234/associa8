document.addEventListener("DOMContentLoaded", () => {
  const trigger = document.querySelector("[data-video-src]");
  const modal = document.getElementById("videoModal");
  const player = modal?.querySelector(".video-modal-player");
  const closeButtons = modal?.querySelectorAll("[data-video-close]");

  if (!trigger || !modal || !player || !closeButtons?.length) return;

  const closeModal = () => {
    player.pause();
    player.removeAttribute("src");
    player.load();
    modal.classList.remove("active");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("video-modal-open");
    trigger.focus();
  };

  const openModal = () => {
    player.src = trigger.dataset.videoSrc;
    if (trigger.dataset.videoPoster)
      player.poster = trigger.dataset.videoPoster;
    modal.classList.add("active");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("video-modal-open");
    player.play().catch(() => {});
  };

  trigger.addEventListener("click", openModal);
  closeButtons.forEach((button) =>
    button.addEventListener("click", closeModal),
  );
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("active"))
      closeModal();
  });
});
