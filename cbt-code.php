<?php
session_start();
$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Start CBT Assessment - Associa8</title>
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
      .cbt-code-page { min-height: 100vh; display: grid; place-items: center; padding: 2rem 1.5rem 4rem; background: #f8f9fc; }
      .cbt-header { display: flex; align-items: center; justify-content: space-between; padding: .75rem 2rem; background: #f8f9fc; }
      .cbt-header-logo img { width: clamp(60px, 10vw, 80px); height: auto; }
      .cbt-home-link { display: inline-flex; align-items: center; gap: .5rem; padding: .65rem 1rem; border: 1px solid var(--color-dark-blue); border-radius: var(--radius-pill); color: var(--color-dark-blue); font-size: .9rem; font-weight: 700; }
      .cbt-home-link:hover { background: rgba(10, 34, 68, .08); }
      .cbt-code-card { width: min(100%, 540px); padding: 3rem; background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-card-strong); text-align: center; }
      .cbt-code-icon { width: 64px; height: 64px; margin: 0 auto 1.5rem; display: grid; place-items: center; border-radius: 50%; color: #fff; background: var(--color-dark-blue); font-size: 1.5rem; }
      .cbt-code-card h1 { margin-bottom: .75rem; }
      .cbt-code-card p { color: var(--color-text-muted); margin-bottom: 1.75rem; }
      .cbt-code-form { text-align: left; }
      .cbt-code-form label { display: block; margin-bottom: .5rem; font-weight: 700; color: var(--color-dark-blue); }
      .cbt-code-form input[type="email"] { width: 100%; padding: .9rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font: inherit; margin-bottom: 20px; }
      .cbt-code-submit { width: 100%; border: 0; cursor: pointer; }
      @media (max-width: 480px) { .cbt-header { padding: .65rem 1rem; } .cbt-code-page { padding-top: 1rem; } .cbt-code-card { padding: 2rem 1.25rem; } }
    </style>
  </head>
  <body>
    <header class="cbt-header">
      <a class="cbt-header-logo" href="index.php" aria-label="Associa8 home">
        <img src="images/brand-logo-dark.png" alt="Associa8" />
      </a>
      <a class="cbt-home-link" href="index.php"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
    </header>
    <main class="cbt-code-page">
      <section class="cbt-code-card" aria-labelledby="cbtCodeHeading">
        <div class="cbt-code-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></div>
        <h1 id="cbtCodeHeading">Start your CBT assessment</h1>
        <p>Enter the email address used on your application. We will send your access code to that email.</p>
        <form class="cbt-code-form" action="proc-validate-cbt-email.php" method="POST">
          <label for="applicantEmail">Email address</label>
          <input type="email" id="applicantEmail" name="email" autocomplete="email" required />
          <button class="btn btn-primary cbt-code-submit" type="submit">Email me my access code</button>
        </form>
      </section>
    </main>
    <div class="app-modal app-modal-info" id="appModal" aria-hidden="true">
      <div class="app-modal-backdrop" data-modal-close></div>
      <div class="app-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="appModalTitle" tabindex="-1">
        <button class="app-modal-close" type="button" data-modal-close aria-label="Close dialog"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        <div class="app-modal-icon" aria-hidden="true"></div>
        <h2 class="app-modal-title" id="appModalTitle"></h2>
        <p class="app-modal-message"></p>
        <p class="app-modal-details" hidden></p>
        <div class="app-modal-actions">
          <button class="app-modal-button app-modal-cancel" type="button" data-modal-close>Close</button>
          <button class="app-modal-button app-modal-confirm" type="button" data-modal-confirm-action hidden>Confirm</button>
        </div>
      </div>
    </div>
    <script src="/associa8/js/modal.js?v=20260826-3"></script>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        if (status && window.AppModal) {
          const success = status === "success";
          window.AppModal.open({
            type: success ? "success" : "error",
            heading: success ? "Email accepted" : "Email not accepted",
            body: params.get("msg") || "Please check your details and try again.",
          });
          window.history.replaceState({}, document.title, window.location.pathname);
        }
      });
    </script>
  </body>
</html>
