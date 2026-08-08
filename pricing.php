<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Associa8 — Pricing</title>
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
    <header class="hero hero-pricing">
      <div class="hero-bg hero-bg-pricing"></div>

      <!-- Full-width wrapper for the dynamic fixed blur header -->
      <div class="navbar-wrapper" id="navbarWrapper">
        <nav class="navbar container">
          <a href="index.php" class="logo">
            <img src="images/brand-logo.png" alt="Brand logo" />
          </a>
          <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="#">Pricing</a></li>
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
        <div class="hero-pricing-content">
          <h1>Pricing</h1>
        </div>
      </div>
    </header>

    <!-- ============================= -->
    <!-- SECTION 2: PRICING PLANS -->
    <!-- ============================= -->
    <section class="pricing" id="pricing">
      <div class="diamond diamond-lg left">
        <span class="diamond-1"></span>
        <span class="diamond-2"></span>
        <span class="diamond-3"></span>
      </div>
      <div class="diamond diamond-lg right">
        <span class="diamond-1"></span>
        <span class="diamond-2"></span>
        <span class="diamond-3"></span>
      </div>
      <div class="container">
        <div class="section-header">
          <div class="eyebrow center pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Pricing Plan
            <span class="pointer">
              <span class="block"></span>
              <hr />
            </span>
          </div>
          <h2>Explore Our Affordable<br />Price</h2>
        </div>

        <div class="pricing-grid">
          <article class="pricing-card">
            <div class="price-desc">
              <h3>Basic</h3>
              <p class="pricing-description">
                For small associations that need to manage members and track
                attendance without the extras.
              </p>
            </div>
            <div class="price-content">
              <p class="price">
                <span class="price-amount">$49</span><span class="slash">/</span
                ><span class="price-period">month</span>
              </p>

              <a href="#" class="btn btn-accent">Start a Free Trail</a>
              <p class="inc">Includes:</p>
              <ul class="pricing-features">
                <li>Up to 100 members</li>
                <li>Member directory</li>
                <li>Attendance tracking</li>
                <li>Basic reporting</li>
                <li>Email support</li>
              </ul>
            </div>
          </article>

          <article class="pricing-card pricing-card-highlight">
            <div class="price-desc">
              <span class="badge">Most Popular</span>
              <h3>Professional</h3>
              <p class="pricing-description">
                For growing associations that need membership, finance, and
                admissions working together.
              </p>
            </div>
            <div class="price-content">
              <p class="price">
                <span class="price-amount">$99</span><span class="slash">/</span
                ><span class="price-period">month</span>
              </p>

              <a href="#" class="btn btn-accent">Start a Free Trail</a>
              <p class="inc">Includes:</p>
              <ul class="pricing-features">
                <li>Everything in Basic</li>
                <li>Up to 500 members</li>
                <li>Financial tracking</li>
                <li>Administration management</li>
                <li>Priority email support</li>
              </ul>
            </div>
          </article>

          <article class="pricing-card">
            <div class="price-desc">
              <h3>Elite</h3>
              <p class="pricing-description">
                For full-scale associations that run every operation through
                Organizations.
              </p>
            </div>
            <div class="price-content">
              <p class="price">
                <span class="price-amount">$199</span
                ><span class="slash">/</span
                ><span class="price-period">month</span>
              </p>

              <a href="#" class="btn btn-accent">Start a Free Trail</a>
              <p class="inc">Includes:</p>
              <ul class="pricing-features">
                <li>Everything in professional</li>
                <li>Unlimited members</li>
                <li>Custom report</li>
                <li>API access</li>
                <li>Phone &amp; email support</li>
              </ul>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 3: PLAN COMPARISON TABLE -->
    <!-- ============================= -->
    <section class="pricing-compare">
      <div class="container">
        <div class="compare-table-wrap">
          <table class="compare-table">
            <thead>
              <tr>
                <th class="compare-feature-head"></th>
                <th>Basic Plan</th>
                <th>Professional Plan</th>
                <th>Elite Plan</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="compare-feature">Price</td>
                <td>$49</td>
                <td>$99</td>
                <td>$199</td>
              </tr>
              <tr>
                <td class="compare-feature">Member Directory</td>
                <td>Up to 100 members</td>
                <td>Up to 500 members</td>
                <td>Unlimited</td>
              </tr>
              <tr>
                <td class="compare-feature">Attendance Tracking</td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Email Support</td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Priority Email Support</td>
                <td><span class="check"></span></td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Financial Tracking</td>
                <td><span class="check"></span></td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Basic Reporting</td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Administration Management</td>
                <td><span class="check"></span></td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">API Access</td>
                <td><span class="check"></span></td>
                <td><span class="check"></span></td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Custom Report</td>
                <td><span class="check"></span></td>
                <td><span class="check"></span></td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
              <tr>
                <td class="compare-feature">Phone &amp; Email Support</td>
                <td><span class="check"></span></td>
                <td><span class="check"></span></td>
                <td>
                  <span class="check checked"
                    ><iconify-icon icon="mdi:check-bold"></iconify-icon
                  ></span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 4: SITE FOOTER -->
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

    <script src="js/fixedtop.js"></script>
  </body>
</html>
