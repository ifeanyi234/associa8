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
      <?php include("inc/navbar.php")?>

      <div class="container">
        <div class="hero-pricing-content">
          <h1>Pricing</h1>
        </div>
      </div>
    </header>
    <?php include("inc/mobile-nav.php") ?>
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
              <span class="block"></span>
              <hr />
            </span>
            Pricing Plan
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
          </div>
          <h2>Explore Our Affordable<br />Price</h2>
        </div>

        <div class="pricing-grid">
          <article class="pricing-card">
            <div class="price-desc">
              <h3>Basic<span class="pointer">
              <hr />
              <span class="block"></span>
            </span></h3>
              
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
              <p class="inc">includes:</p>
              <ul class="pricing-features">
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Up to 100 members</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Member directory</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Attendance tracking</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Basic reporting</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Email support</li>
              </ul>
            </div>
          </article>

          <article class="pricing-card pricing-card-highlight">
            <div class="price-desc">
              <span class="badge">Most Popular</span>
              <h3>Professional<span class="pointer">
              <hr />
              <span class="block"></span>
            </span></h3>
              <p class="pricing-description">
                For growing associations that need deeper financial and
                administrative control.
              </p>
            </div>
            <div class="price-content">
              <p class="price">
                <span class="price-amount">$99</span><span class="slash">/</span
                ><span class="price-period">month</span>
              </p>

              <a href="#" class="btn btn-accent">Start a Free Trail</a>
              <p class="inc">includes:</p>
              <ul class="pricing-features">
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Up to 100 members</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Member directory</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Attendance tracking</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Basic reporting</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Email support</li>
              </ul>
            </div>
          </article>

          <article class="pricing-card">
            <div class="price-desc">
              <h3>Elite<span class="pointer">
              <hr />
              <span class="block"></span>
            </span></h3>
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
              <p class="inc">includes:</p>
              <ul class="pricing-features">
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Up to 100 members</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Member directory</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Attendance tracking</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Basic reporting</li>
                <li><iconify-icon icon="mdi:checkbox-marked"></iconify-icon> Email support</li>
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
    <?php include("inc/footer.php")?>
    <script src="js/mobilemenu.js"></script>
    <script src="js/fixedtop.js"></script>
  </body>
</html>
