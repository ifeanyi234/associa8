<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Associa8 — Contact</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/styles.css" />
    <!-- Iconify for Icons -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  </head>
  <body>
    <!-- ============================= -->
    <!-- SECTION 1: HERO -->
    <!-- ============================= -->
    <header class="hero hero-contact">
      <div class="hero-bg hero-bg-contact"></div>

      <!-- Full-width wrapper for the dynamic fixed blur header -->
      <?php include("inc/navbar.php")?>

      <div class="container">
        <div class="hero-contact-content">
          <h1>Contact</h1>
        </div>
      </div>
    </header>
    <?php include("inc/mobile-nav.php") ?>
    <!-- ============================= -->
    <!-- SECTION 2: CONTACT INFO + FORM -->
    <!-- ============================= -->
    <section class="contact-section">
      <div class="container contact-grid">
        <div class="contact-intro">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Get In Touch
          </div>
          <h2>
            Our team is here to help you get started, and ensure you get the
            most out of Associa8.
          </h2>

          <ul class="contact-info-list">
            <li class="contact-info-item">
              <span class="contact-icon" aria-hidden="true">
                <iconify-icon icon="mdi:map-marker"></iconify-icon>
              </span>
              <div>
                <h4>Address</h4>
                <p>25, Kingsway avenue Ikoyi, Lagos Nigeria</p>
              </div>
            </li>

            <li class="contact-info-item">
              <span class="contact-icon" aria-hidden="true">
                <iconify-icon icon="mdi:phone"></iconify-icon>
              </span>
              <div>
                <h4>Phone</h4>
                <p>
                  +234-80-123456789<br />
                  +234-80-123456789
                </p>
              </div>
            </li>

            <li class="contact-info-item">
              <span class="contact-icon" aria-hidden="true">
                <iconify-icon icon="mdi:email"></iconify-icon>
              </span>
              <div>
                <h4>Email</h4>
                <p>
                  Info@associa8.com.ng<br />
                  associa8@info.co.uk
                </p>
              </div>
            </li>
          </ul>
        </div>

        <div class="contact-form-card">
          <div class="contact-form-header">
            <div class="eyebrow pointer-holder">
              <span class="pointer">
                <hr />
                <span class="block"></span>
              </span>
              Have Questions or Need Assistance
            </div>
          </div>

          <form class="contact-form" onsubmit="return false;">
            <div class="form-grid">
              <div class="form-group">
                <label for="fullName">Full Name</label>
                <input
                  type="text"
                  id="fullName"
                  name="fullName"
                  placeholder="Enter your names"
                  required
                />
              </div>

              <div class="form-group">
                <label for="orgName">Name of Organization</label>
                <input
                  type="text"
                  id="orgName"
                  name="orgName"
                  placeholder="Enter organization name"
                />
              </div>

              <div class="form-group">
                <label for="orgType">Type of organization</label>
                <select id="orgType" name="orgType" required>
                  <option value="" selected disabled hidden>
                    Select organization
                  </option>
                  <option value="professional-association">
                    Professional Association
                  </option>
                  <option value="church">Church</option>
                  <option value="ngo">NGO / Non-Profit</option>
                  <option value="alumni">Alumni Network</option>
                  <option value="club">Club / Community Group</option>
                </select>
              </div>

              <div class="form-group">
                <label for="phone">Phone</label>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  placeholder="Enter number"
                />
              </div>

              <div class="form-group">
                <label for="email">Email</label>
                <input
                  type="email"
                  id="email"
                  name="email"
                  placeholder="Enter your email"
                  required
                />
              </div>

              <div class="form-group">
                <label for="location">Location</label>
                <select id="location" name="location" required>
                  <option value="" selected disabled hidden>
                    Select location
                  </option>
                  <option value="lagos">Lagos</option>
                  <option value="abuja">Abuja</option>
                  <option value="port-harcourt">Port Harcourt</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="form-group full-width">
                <label for="message">Additional Information</label>
                <textarea
                  id="message"
                  name="message"
                  placeholder="Type your message"
                ></textarea>
              </div>
            </div>

            <button type="submit" class="btn btn-dark">Submit</button>
          </form>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 3: SITE FOOTER -->
    <!-- ============================= -->
    <?php include("inc/footer.php")?>
    <script src="js/mobilemenu.js"></script>
    <script src="js/fixedtop.js"></script>
  </body>
</html>
