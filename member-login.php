<?php
session_start();
$loginError = '';
if (isset($_GET['error'])) {
    $errorCode = (int) $_GET['error'];
    if ($errorCode === 1) {
        $loginError = 'Please enter both your email and password.';
    } elseif ($errorCode === 2) {
        $loginError = 'This member account has not been assigned a password yet. Please contact the association.';
    } elseif ($errorCode === 3) {
        $loginError = 'This member account is suspended and cannot log in.';
    } else {
        $loginError = 'Incorrect email or password. Please try again.';
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Member Login - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="css/styles.css" />
  </head>
  <body class="auth-body">
    <div class="auth-wrapper">
      <div class="auth-overlay"></div>

      <div class="auth-container">
        <div class="auth-left">
          <div class="auth-brand">
            <a href="index.php" class="">
              <img src="images/brand-logo-white.png" alt="white brand logo" />
            </a>
          </div>

          <div class="auth-welcome">
            <h1>Member Portal</h1>
            <p>
              Sign in to manage your profile, attendance, documents and updates.
            </p>
          </div>
        </div>

        <div class="auth-card">
          <h2 class="auth-title">Member Login</h2>
          <?php if ($loginError !== ''): ?>
            <p class="login-error-message"><?php echo htmlspecialchars($loginError); ?></p>
          <?php endif; ?>

          <form class="auth-form" action="proc-member-login.php" method="POST">
            <div class="form-group">
              <label for="email">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
                autocomplete="email"
              />
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-field-wrapper">
                <input
                  type="password"
                  id="password"
                  name="password"
                  placeholder="Enter password"
                  required
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="btn-toggle-password"
                  id="togglePasswordBtn"
                  aria-label="Toggle password visibility"
                >
                  <iconify-icon id="eyeIcon" icon="mdi:eye-off-outline"></iconify-icon>
                </button>
              </div>
            </div>

            <div class="auth-options">
              <label class="remember-me">
                <input type="checkbox" id="remember" name="remember" />
                <span class="checkbox-custom"></span>
                <span>Remember Me</span>
              </label>
              <a href="index.php" class="forgot-password">Back home</a>
            </div>

            <button type="submit" class="btn-auth-submit">Sign in</button>
          </form>
        </div>
      </div>
    </div>

    <script>
      const togglePasswordBtn = document.getElementById('togglePasswordBtn');
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');

      if (togglePasswordBtn && passwordInput && eyeIcon) {
        togglePasswordBtn.addEventListener('click', () => {
          const isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          eyeIcon.setAttribute('icon', isPassword ? 'mdi:eye-outline' : 'mdi:eye-off-outline');
        });
      }
    </script>
  </body>
</html>
