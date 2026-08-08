<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Your Account - Associa8</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />

    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="css/styles.css" />
  </head>
  <body>
    <!-- ============================= -->
    <!-- SECTION 1: HERO -->
    <!-- ============================= -->
    <header class="hero hero-signup">
      <div class="hero-bg hero-bg-signup"></div>

      <div class="navbar-wrapper" id="navbarWrapper">
        <nav class="navbar container">
          <a href="index.php" class="logo">
            <img src="images/brand-logo.png" alt="Brand logo" />
          </a>

          <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="pricing.php">Pricing</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>

          <div class="nav-actions">
            <a href="admin/index.php" class="btn btn-outline-light" target="_blank"
              >Login</a
            >
            <a href="signup.php" class="btn btn-white">Sign Up</a>
          </div>
        </nav>
      </div>

      <div class="container">
        <div class="hero-signup-content">
          <h1>Register Your Account</h1>
        </div>
      </div>
    </header>

    <!-- ============================= -->
    <!-- SECTION 2: REGISTRATION FORM -->
    <!-- ============================= -->
    <section class="signup-section">
      <span
        class="signup-corner-shape signup-corner-top"
        aria-hidden="true"
      ></span>
      <span
        class="signup-corner-shape signup-corner-bottom"
        aria-hidden="true"
      ></span>

      <div class="container">
        <div class="signup-intro">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Create Your Organization
          </div>
          <h2>
            Get started with Associa8 by creating your organization and
            administrator account.
          </h2>
        </div>

        <div class="signup-card">
          <!-- Step Header -->
          <div class="signup-card-header">
            <div class="stepper-wrapper">
              <div class="stepper-line"></div>
              <div class="stepper-step active" data-step="1">1</div>
              <div class="stepper-step" data-step="2">2</div>
              <div class="stepper-step" data-step="3">3</div>
            </div>
            <div class="step-title-wrap">
              <p id="stepHeaderTitle" class="step-title">
                Organization Information
              </p>
              <span class="step-title-underline"></span>
            </div>
          </div>

          <!-- Form Body -->
          <div class="signup-card-body">
            <form
              id="signupForm"
              action="#"
              method="POST"
              onsubmit="event.preventDefault()"
            >
              <!-- STEP 1: ORGANIZATION INFORMATION -->
              <div class="form-step active" id="step-1">
                <div class="signup-grid">
                  <div class="form-group">
                    <label for="orgName"
                      >Name of Organization
                      <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="orgName"
                      name="orgName"
                      placeholder="Enter organization name"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="orgType"
                      >Type of Organization
                      <span class="required">*</span></label
                    >
                    <select id="orgType" name="orgType" required>
                      <option value="" disabled selected hidden>
                        Select organization type
                      </option>
                      <option value="association">Association</option>
                      <option value="church">
                        Church / Religious Institution
                      </option>
                      <option value="ngo">NGO / Non-Profit</option>
                      <option value="club">Club / Society</option>
                      <option value="corporate">Corporate / Business</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="orgEmail"
                      >Email <span class="required">*</span></label
                    >
                    <input
                      type="email"
                      id="orgEmail"
                      name="orgEmail"
                      placeholder="Enter organization email"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="orgPhone"
                      >Phone <span class="required">*</span></label
                    >
                    <input
                      type="tel"
                      id="orgPhone"
                      name="orgPhone"
                      placeholder="Enter organization number"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="orgCountry"
                      >Country <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="orgCountry"
                      name="orgCountry"
                      placeholder="Enter organization country"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="orgState"
                      >State / Province <span class="required">*</span></label
                    >
                    <select id="orgState" name="orgState" required>
                      <option value="" disabled selected hidden>
                        Select state / province
                      </option>
                      <option value="state1">State 1</option>
                      <option value="state2">State 2</option>
                      <option value="state3">State 3</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="orgPricing"
                      >Pricing <span class="required">*</span></label
                    >
                    <select id="orgPricing" name="orgPricing" required>
                      <option value="" disabled selected hidden>
                        Select pricing plan
                      </option>
                      <option value="basic">Basic Plan</option>
                      <option value="professional">Professional Plan</option>
                      <option value="elite">Elite Plan</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="orgMembers"
                      >Total Numbers of Members
                      <span class="required">*</span></label
                    >
                    <input
                      type="number"
                      id="orgMembers"
                      name="orgMembers"
                      placeholder="Select number"
                      required
                    />
                  </div>
                </div>

                <div class="step-actions right-only">
                  <button
                    type="button"
                    class="btn-step-next"
                    onclick="goToStep(2)"
                  >
                    Next
                  </button>
                </div>
              </div>

              <!-- STEP 2: ADMINISTRATION INFO -->
              <div class="form-step" id="step-2">
                <div class="signup-grid">
                  <div class="form-group">
                    <label for="adminFirstName"
                      >First Name <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="adminFirstName"
                      name="adminFirstName"
                      placeholder="Enter first name"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="adminLastName"
                      >Last Name <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="adminLastName"
                      name="adminLastName"
                      placeholder="Enter last name"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="adminEmail"
                      >Email <span class="required">*</span></label
                    >
                    <input
                      type="email"
                      id="adminEmail"
                      name="adminEmail"
                      placeholder="Enter email"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="adminPhone"
                      >Phone <span class="required">*</span></label
                    >
                    <input
                      type="tel"
                      id="adminPhone"
                      name="adminPhone"
                      placeholder="Enter number"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="adminJobTitle"
                      >Job Title <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="adminJobTitle"
                      name="adminJobTitle"
                      placeholder="Enter job title"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label for="adminRole"
                      >Role <span class="required">*</span></label
                    >
                    <select id="adminRole" name="adminRole" required>
                      <option value="" disabled selected hidden>
                        Select role
                      </option>
                      <option value="super_admin">Super Administrator</option>
                      <option value="admin">Administrator</option>
                      <option value="manager">Manager</option>
                    </select>
                  </div>
                </div>

                <div class="step-actions space-between">
                  <button
                    type="button"
                    class="btn-step-back"
                    onclick="goToStep(1)"
                  >
                    Back
                  </button>
                  <button
                    type="button"
                    class="btn-step-next"
                    onclick="goToStep(3)"
                  >
                    Next
                  </button>
                </div>
              </div>

              <!-- STEP 3: ACCOUNT INFORMATION -->
              <div class="form-step" id="step-3">
                <div class="signup-grid">
                  <div class="form-group">
                    <label for="accUsername"
                      >Username <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="accUsername"
                      name="accUsername"
                      placeholder="Enter username"
                      required
                      autocomplete="username"
                    />
                  </div>

                  <div class="form-group">
                    <label for="accPassword"
                      >Password <span class="required">*</span></label
                    >
                    <input
                      type="password"
                      id="accPassword"
                      name="accPassword"
                      placeholder="Enter password"
                      required
                      autocomplete="new-password"
                    />
                  </div>

                  <div class="form-group">
                    <label for="accConfirmPassword"
                      >Confirm Password <span class="required">*</span></label
                    >
                    <input
                      type="password"
                      id="accConfirmPassword"
                      name="accConfirmPassword"
                      placeholder="Enter password"
                      required
                      autocomplete="new-password"
                    />
                  </div>

                  <div class="form-group">
                    <label for="accOtp"
                      >OTP <span class="required">*</span></label
                    >
                    <input
                      type="text"
                      id="accOtp"
                      name="accOtp"
                      placeholder="Enter OTP"
                      required
                      maxlength="6"
                    />
                  </div>
                </div>

                <div class="step-actions space-between">
                  <button
                    type="button"
                    class="btn-step-back"
                    onclick="goToStep(2)"
                  >
                    Back
                  </button>
                  <button type="submit" class="btn-step-submit">
                    Create Account
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 3: SITE FOOTER -->
    <!-- ============================= -->
    <footer class="site-footer">
      <div class="container footer-top">
        <div class="footer-brand">
          <a href="#" class="logo">
            <img src="images/brand-logo.png" alt="Brand logo" />
          </a>
          <p>
            Empowering associations, churches, clubs, NGOs, and organizations
            with an all-in-one platform to manage memberships, finances, events,
            attendance, and communication.
          </p>
        </div>

        <div class="footer-links">
          <div class="footer-links-group">
            <h4>Features</h4>
            <ul>
              <li><a href="#">Membership</a></li>
              <li><a href="#">Finance</a></li>
              <li><a href="#">Attendance</a></li>
              <li><a href="#">Events</a></li>
              <li><a href="#">Communication</a></li>
            </ul>
          </div>

          <div class="footer-links-group">
            <h4>Resources</h4>
            <ul>
              <li><a href="#">Blog</a></li>
              <li><a href="#">FAQ</a></li>
              <li><a href="#">Help</a></li>
              <li><a href="#">Privacy policy</a></li>
              <li><a href="#">Terms of service</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-newsletter">
          <h4>Get the Latest Information</h4>
          <form class="newsletter-form" onsubmit="return false;">
            <label for="newsletter-email" class="sr-only">Email address</label>
            <input
              type="email"
              id="newsletter-email"
              placeholder="Enter your email"
              required
            />
            <button type="submit" class="btn btn-accent">Submit</button>
          </form>
        </div>
      </div>

      <div class="container">
        <div class="footer-bottom">
          <p>Copyright &copy; 2026 Associa8. All rights reserved</p>
          <div class="social-icons">
            <a href="#" class="social-icon" aria-label="Facebook"></a>
            <a href="#" class="social-icon" aria-label="Twitter"></a>
            <a href="#" class="social-icon" aria-label="Instagram"></a>
            <a href="#" class="social-icon" aria-label="LinkedIn"></a>
          </div>
        </div>
      </div>
    </footer>

    <!-- ================= MULTI-STEP INTERACTION SCRIPT ================= -->
    <script>
      var stepTitles = {
        1: "Organization Information",
        2: "Administration Info",
        3: "Account Information",
      };

      function goToStep(stepNumber) {
        document.querySelectorAll(".form-step").forEach(function (step) {
          step.classList.remove("active");
        });

        var targetStep = document.getElementById("step-" + stepNumber);
        if (targetStep) {
          targetStep.classList.add("active");
        }

        document
          .querySelectorAll(".stepper-wrapper .stepper-step")
          .forEach(function (item) {
            var itemStep = parseInt(item.getAttribute("data-step"), 10);
            item.classList.toggle("active", itemStep === stepNumber);
          });

        var headerTitle = document.getElementById("stepHeaderTitle");
        if (headerTitle && stepTitles[stepNumber]) {
          headerTitle.textContent = stepTitles[stepNumber];
        }

        document
          .querySelector(".signup-card")
          .scrollIntoView({ behavior: "smooth", block: "nearest" });
      }
    </script>
    <script src="js/fixedtop.js"></script>
  </body>
</html>
