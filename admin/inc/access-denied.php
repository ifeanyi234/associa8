<?php
function admin_render_access_denied(string $message = "Your current account doesn't have access to this page. If you need access, contact your organization administrator."): void
{
    http_response_code(403);
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6f8fb">
  <title>Access unavailable - Associa8</title>
  <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      margin: 0;
      padding: 2rem;
      display: grid;
      place-items: center;
      background:
        radial-gradient(ellipse at 14% 12%, rgba(37, 99, 235, .08), transparent 30rem),
        #f6f8fb;
      color: #14243b;
      font-family: "Manrope", sans-serif;
      -webkit-font-smoothing: antialiased;
    }
    .access-card {
      width: min(100%, 720px);
      overflow: hidden;
      border: 1px solid #e7edf5;
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 22px 65px rgba(17, 38, 71, .1);
    }
    .access-brand {
      display: flex;
      align-items: center;
      min-height: 76px;
      padding: 1.25rem 2rem;
      border-bottom: 1px solid #eef1f6;
    }
    .access-brand img { display: block; width: 108px; height: auto; }
    .access-content {
      display: grid;
      grid-template-columns: minmax(145px, .7fr) minmax(0, 1.3fr);
      align-items: center;
      gap: 2rem;
      padding: 3rem 3.25rem 3.25rem;
    }
    .access-art {
      position: relative;
      display: grid;
      min-height: 190px;
      place-items: center;
      border: 1px solid #e7effc;
      border-radius: 18px;
      background: linear-gradient(145deg, #f4f8ff, #edf3fd);
    }
    .access-art::before, .access-art::after {
      position: absolute;
      width: 64px;
      height: 64px;
      border: 1px solid rgba(37, 99, 235, .15);
      border-radius: 18px;
      content: "";
      transform: rotate(18deg);
    }
    .access-art::after { width: 92px; height: 92px; border-color: rgba(37, 99, 235, .09); transform: rotate(42deg); }
    .access-code {
      z-index: 1;
      color: #1e56c5;
      font-size: clamp(2.7rem, 7vw, 3.6rem);
      font-weight: 800;
      letter-spacing: -.08em;
    }
    .access-copy .eyebrow {
      margin: 0 0 .65rem;
      color: #2563eb;
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .13em;
      text-transform: uppercase;
    }
    .access-copy h1 {
      margin: 0;
      color: #10233f;
      font-size: clamp(1.6rem, 4vw, 2rem);
      font-weight: 800;
      line-height: 1.2;
      letter-spacing: -.045em;
    }
    .access-copy p {
      margin: .9rem 0 0;
      color: #536278;
      font-size: .92rem;
      line-height: 1.75;
    }
    .access-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.5rem; }
    .access-button {
      display: inline-flex;
      min-height: 44px;
      align-items: center;
      justify-content: center;
      padding: .7rem 1rem;
      border: 1px solid #1e56c5;
      border-radius: 9px;
      background: #1e56c5;
      color: #fff;
      font-size: .84rem;
      font-weight: 800;
      text-decoration: none;
      cursor: pointer;
      font-family: inherit;
      transition: background .15s ease, transform .15s ease;
    }
    .access-button:hover { transform: translateY(-1px); background: #1748a8; }
    .access-button:focus-visible { outline: 3px solid rgba(37, 99, 235, .3); outline-offset: 3px; }
    .access-button-secondary { border-color: #dce4ef; background: #fff; color: #35455d; }
    .access-button-secondary:hover { background: #f7f9fc; }
    @media (max-width: 600px) {
      body { padding: 1rem; }
      .access-brand { min-height: 66px; padding: 1rem 1.25rem; }
      .access-content { grid-template-columns: 1fr; gap: 1.5rem; padding: 1.5rem 1.25rem 1.75rem; }
      .access-art { min-height: 120px; }
      .access-code { font-size: 2.7rem; }
    }
  </style>
</head>
<body>
  <main class="access-card">
    <div class="access-brand"><img src="../images/brand-logo-dark.png" alt="Associa8"></div>
    <div class="access-content">
      <div class="access-art" aria-hidden="true"><span class="access-code">403</span></div>
      <section class="access-copy">
        <p class="eyebrow">Access unavailable</p>
        <h1>This page isn’t available to your account.</h1>
        <p>' . $safeMessage . '</p>
        <nav class="access-actions" aria-label="Access options">
          <a class="access-button" href="dashboard.php">Return to dashboard</a>
          <button class="access-button access-button-secondary" type="button" onclick="history.back()">Go back</button>
        </nav>
      </section>
    </div>
  </main>
</body>
</html>';
    exit;
}
