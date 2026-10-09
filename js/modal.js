document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("appModal");
  if (!modal) return;

  const dialog = modal.querySelector(".app-modal-dialog");
  const icon = modal.querySelector(".app-modal-icon");
  const title = modal.querySelector(".app-modal-title");
  const message = modal.querySelector(".app-modal-message");
  const details = modal.querySelector(".app-modal-details");
  const closeButtons = modal.querySelectorAll("[data-modal-close]");
  const confirmButton = modal.querySelector("[data-modal-confirm-action]");
  const confirmedForms = new WeakSet();
  let lastTrigger = null;
  let confirmAction = null;

  const close = () => {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("app-modal-open");
    confirmAction = null;
    if (lastTrigger) lastTrigger.focus();
  };

  const open = ({
    type = "info",
    heading,
    body,
    detail = "",
    confirm = false,
    onConfirm = null,
    trigger = null,
  }) => {
    lastTrigger = trigger || document.activeElement;
    modal.className = `app-modal app-modal-${type}`;
    icon.innerHTML =
      {
        error: '<span aria-hidden="true">&times;</span>',
        success: '<span aria-hidden="true">&#10003;</span>',
        warning: '<span aria-hidden="true">!</span>',
        info: '<span aria-hidden="true">i</span>',
      }[type] || '<span aria-hidden="true">i</span>';
    title.textContent = heading;
    message.textContent = body;
    details.textContent = detail;
    details.hidden = !detail;
    confirmButton.hidden = !confirm;
    confirmAction = onConfirm;
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("app-modal-open");
    requestAnimationFrame(() => modal.classList.add("is-open"));
    dialog.focus();
  };

  closeButtons.forEach((button) => button.addEventListener("click", close));
  confirmButton.addEventListener("click", () => {
    if (confirmAction) confirmAction();
    close();
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("is-open")) close();
  });

  document.addEventListener(
    "submit",
    (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement) || !form.hasAttribute("data-app-modal-confirm")) return;
      if (confirmedForms.has(form)) {
        confirmedForms.delete(form);
        return;
      }

      event.preventDefault();
      const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
      open({
        type: "warning",
        heading: form.dataset.confirmTitle || "Please confirm",
        body: form.dataset.appModalConfirm,
        detail: form.dataset.confirmDetail || "Please confirm to continue.",
        confirm: true,
        trigger: submitter,
        onConfirm: () => {
          confirmedForms.add(form);
          try {
            form.requestSubmit(submitter || undefined);
          } finally {
            confirmedForms.delete(form);
          }
        },
      });
    },
    true,
  );

  window.AppModal = { open, close };
});
