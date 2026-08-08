<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Associa8</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css" />
  </head>
  <body class="auth-body">
    <div class="auth-wrapper">
      <!-- Dark overlay for background image contrast -->
      <div class="auth-overlay"></div>

      <div class="auth-container">
        <!-- Left Column: Branding & Welcome Text -->
        <div class="auth-left">
          <div class="auth-brand">
            <a href="index.php" class="">
              <!-- Associa8 Logo Icon -->
              <img src="../images/brand-logo-white.png" alt="white brand logo" />
            </a>
          </div>

          <div class="auth-welcome">
            <h1>Welcome to Associa8!</h1>
            <p>
              Your all in one platform for managing associations and communities
              with confidence.
            </p>
          </div>
        </div>

        <!-- Right Column: Login Card -->
        <div class="auth-card">
          <h2 class="auth-title">Login</h2>

          <form
            class="auth-form"
            action="#"
            method="POST"
            onsubmit="event.preventDefault()"
          >
            <!-- Username Input -->
            <div class="form-group">
              <label for="username">Username</label>
              <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter username"
                required
                autocomplete="username"
              />
            </div>

            <!-- Password Input -->
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
                  <iconify-icon
                    id="eyeIcon"
                    icon="mdi:eye-off-outline"
                  ></iconify-icon>
                </button>
              </div>
            </div>

            <!-- Options Row -->
            <div class="auth-options">
              <label class="remember-me">
                <input type="checkbox" id="remember" name="remember" />
                <span class="checkbox-custom"></span>
                <span>Remember Me</span>
              </label>
              <a href="#" class="forgot-password">Forget Password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-auth-submit">Sign in</button>

            <!-- Divider -->
            <div class="auth-divider">
              <span>or sign in with</span>
            </div>

            <!-- Social Login Buttons -->
            <div class="social-login-grid">
              <button type="button" class="btn-social">
                <iconify-icon
                  icon="logos:google-icon"
                  width="18"
                ></iconify-icon>
                <span>Google</span>
              </button>
              <button type="button" class="btn-social">
                <iconify-icon
                  icon="ri:apple-fill"
                  width="20"
                  style="color: #ffffff"
                ></iconify-icon>
                <span>Apple</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Interactive Password Toggle Script -->
    <script>
      const togglePasswordBtn = document.getElementById("togglePasswordBtn");
      const passwordInput = document.getElementById("password");
      const eyeIcon = document.getElementById("eyeIcon");

      if (togglePasswordBtn && passwordInput && eyeIcon) {
        togglePasswordBtn.addEventListener("click", () => {
          const isPassword = passwordInput.getAttribute("type") === "password";
          passwordInput.setAttribute("type", isPassword ? "text" : "password");
          eyeIcon.setAttribute(
            "icon",
            isPassword ? "mdi:eye-outline" : "mdi:eye-off-outline",
          );
        });
      }
    </script>
  </body>
</html>
