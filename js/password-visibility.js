document.addEventListener("DOMContentLoaded", () => {
  const eyeIcon =
    '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
  const eyeOffIcon =
    '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle><path d="m3 3 18 18"></path></svg>';

  document.querySelectorAll('input[type="password"]').forEach((input) => {
    let wrapper = input.closest(".password-field-wrapper");
    if (!wrapper) {
      wrapper = document.createElement("div");
      wrapper.className = "password-field-wrapper";
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
    }
    wrapper.classList.add("password-field-wrapper-shared");

    let button = wrapper.querySelector(".btn-toggle-password, .password-visibility-toggle");
    if (!button) {
      button = document.createElement("button");
      wrapper.appendChild(button);
    }

    button.className = "btn-toggle-password password-visibility-toggle";
    button.type = "button";
    button.setAttribute("aria-label", "Show password");
    button.setAttribute("aria-pressed", "false");
    button.innerHTML = eyeOffIcon;
    button.addEventListener("click", () => {
      const reveal = input.type === "password";
      input.type = reveal ? "text" : "password";
      button.setAttribute("aria-label", reveal ? "Hide password" : "Show password");
      button.setAttribute("aria-pressed", String(reveal));
      button.classList.toggle("is-visible", reveal);
      button.innerHTML = reveal ? eyeIcon : eyeOffIcon;
    });
    wrapper.appendChild(button);
  });
});
