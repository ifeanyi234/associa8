<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Associa8 — Manage, Connect, and Grow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/styles.css" />
    <script src="https://code.iconify.design/iconify-icon/3.0.2/iconify-icon.min.js"></script>
  </head>
  <body>
    <!-- ============================= -->
    <!-- SECTION 1: HERO -->
    <!-- ============================= -->
    <header class="hero hero-home">
      <!-- NEW: Full-width wrapper for the blur and fixed effect -->
      <?php include("inc/navbar.php") ?>

      <div class="hero-bg hero-bg-home"></div>
      <!-- ... rest of your hero content ... -->

      <div class="hero-content container">
        <div class="hero-text">
          <p class="eyebrow eyebrow-accent">— Empowering</p>
          <h1>Associations to Manage, Connect, and Grow</h1>
          <p class="hero-description">
            Digitize your association's operations with an all in one platform
            that streamlines administration and keeps every member connected.
          </p>
          <div class="hero-cta">
            <a href="#" class="btn btn-white">Start a Free Trail</a>
            <a href="#" class="btn btn-outline-light">Book a Demo</a>
          </div>
        </div>

        <div class="hero-image">
          <div class="slider-container">
            <img src="images/hero-banner1.jpg" class="slide slide1" alt="" />
            <img src="images/hero-banner2.jpg" class="slide slide2" alt="" />
            <img src="images/hero-banner3.jpg" class="slide slide3" alt="" />
            <img src="images/hero-banner4.jpg" class="slide slide4" alt="" />
            <img src="images/hero-banner5.jpg" class="slide slide4" alt="" />
            <img src="images/hero-banner6.jpg" class="slide slide4" alt="" />
          </div>

          <div class="hero-dots"></div>
        </div>
      </div>
    </header>
    <?php include("inc/mobile-nav.php") ?>
    <!-- ============================= -->
    <!-- SECTION 2: FEATURES -->
    <!-- ============================= -->
    <section class="features">
      <div class="diamond diamond-md">
        <span class="diamond-1"></span>
        <span class="diamond-2"></span>
        <span class="diamond-3"></span>
      </div>
      <div class="diamond diamond-sm">
        <span class="diamond-1"></span>
        <span class="diamond-2"></span>
        <span class="diamond-3"></span>
      </div>
      <div class="diamond diamond-sm down">
        <span class="diamond-1"></span>
        <span class="diamond-2"></span>
        <span class="diamond-3"></span>
      </div>
      <div class="container features-grid">
        <div class="features-cards">
          <article class="feature-card">
            <div class="feature-icon feature-icon-1" aria-hidden="true">
              <iconify-icon icon="lets-icons:user-light"></iconify-icon>
            </div>
            <h3>Membership Management</h3>
            <p>
              Effortlessly manage member profiles, roles, zones, and lifecycle
              from registration to alumni status.
            </p>
          </article>

          <article class="feature-card">
            <div class="feature-icon feature-icon-2" aria-hidden="true">
              <iconify-icon icon="ei:plus"></iconify-icon>
            </div>
            <h3>Admissions Workflow</h3>
            <p>
              Simplify member onboarding with online applications, approval
              workflows, and CBT-based admissions.
            </p>
          </article>

          <article class="feature-card">
            <div class="feature-icon feature-icon-3" aria-hidden="true">
              <iconify-icon icon="hugeicons:cashier-02"></iconify-icon>
            </div>
            <h3>Financial Tracking</h3>
            <p>
              Track dues, payments, levies, and financial reports with complete
              transparency and control.
            </p>
          </article>

          <article class="feature-card">
            <div class="feature-icon feature-icon-4" aria-hidden="true">
              <iconify-icon icon="ph:user-check-light"></iconify-icon>
            </div>
            <h3>Attendance Monitoring</h3>
            <p>
              Monitor meeting attendance, event participation, and member
              compliance with real-time tracking.
            </p>
          </article>
        </div>

        <div class="features-intro">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Our Feature
          </div>
          <h2>Powerful Features Built for Modern Organizations</h2>
          <p class="section-description">
            Associa8 provides everything your organization needs in one secure,
            intuitive, and scalable platform. Focus on building stronger
            communities while we simplify the administration.
          </p>
          <a href="about.php" class="btn btn-accent">Explore more</a>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 3: PROCESS -->
    <!-- ============================= -->
    <section class="process">
      <div class="container">
        <div class="section-header process-section-header">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Our Process
          </div>
          <h2>How Associa8 Works</h2>
          <p class="section-description">
            Get started in just a few simple steps and transform <br> the way your
            organization manages members,<br> operations, and communication.
          </p>
        </div>

        <div class="process-path">
          <span class="process-blob process-blob-1" aria-hidden="true"></span>
          <span class="process-blob process-blob-2" aria-hidden="true"></span>

          <img
            src="images/process-line.png"
            class="process-line"
            alt=""
            aria-hidden="true"
          />

          <span class="step-dot step-dot-1" aria-hidden="true"></span>
          <span class="step-dot step-dot-2" aria-hidden="true"></span>
          <span class="step-dot step-dot-3" aria-hidden="true"></span>

          <article class="process-step process-step-1">
            <span class="process-number" aria-hidden="true">1</span>
            <h3>Create Your Organization</h3>
            <p>
              Creating an account and configuring your profile and
              administrative settings.
            </p>
          </article>

          <article class="process-step process-step-2">
            <span class="process-number" aria-hidden="true">2</span>
            <h3>Manage Members</h3>
            <p>
              Add members, assign roles and organize them into zones, and keep
              member information up to date.
            </p>
          </article>

          <article class="process-step process-step-3">
            <span class="process-number" aria-hidden="true">3</span>
            <h3>Streamline Operations</h3>
            <p>
              Manage admissions, track attendance, oversee finances, share
              documents, and communicate with members.
            </p>
          </article>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 4: PRICING -->
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
          <h2>Flexible Pricing for Every Organization</h2>
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
    <!-- SECTION 5: TESTIMONIALS -->
    <!-- ============================= -->
    <section class="testimonials">
      <div class="container">
        <div class="testimonials-header">
          <div>
            <p class="eyebrow">— Client Review</p>
            <h2>Real feedback from groups using Organizations</h2>
          </div>
          <p class="section-description">
            Hear from association leaders who track members, dues, and
            attendance without the extra work.
          </p>
        </div>

        <div class="testimonials-viewport">
          <div class="testimonials-track" id="testimonialsTrack">
            <article class="testimonial-card">
              <div class="quotes">
                <span class="quote-icon" aria-hidden="true">&ldquo;</span>
                <span class="quote-icon" aria-hidden="true">&rdquo;</span>
              </div>
              
              <p class="testimonial-text">
                Associa8 has completely transformed the way we manage our
                association. From member registration and attendance tracking
                to financial management, everything is finally in one place.
              </p>
              <div class="testimonial-footer">
                <span class="avatar" aria-hidden="true"><img src="images/client1.png" alt="client 1"></span>
                <div>
                  <p class="testimonial-name">Mark W.</p>
                  <p class="testimonial-title">Chairman, Charlton Group</p>
                </div>
              </div>
            </article>

            <article class="testimonial-card">
              <div class="quotes">
                <span class="quote-icon" aria-hidden="true">&ldquo;</span>
                <span class="quote-icon" aria-hidden="true">&rdquo;</span>
              </div>
              <p class="testimonial-text">
                We were drowning in spreadsheets for membership renewals.
                Associa8 let us track payments and send reminders in one
                place. Saves us hours each month.
              </p>
              <div class="testimonial-footer">
                <span class="avatar" aria-hidden="true"><img src="images/client2.png" alt="client 2"></span>
                <div>
                  <p class="testimonial-name">Nneka O.</p>
                  <p class="testimonial-title">
                    Secretary, Community Association
                  </p>
                </div>
              </div>
            </article>

            <article class="testimonial-card">
              <div class="quotes">
                <span class="quote-icon" aria-hidden="true">&ldquo;</span>
                <span class="quote-icon" aria-hidden="true">&rdquo;</span>
              </div>
              <p class="testimonial-text">
                Attendance tracking used to mean passing a sheet around
                meetings. Now members check in on their phones. Simple and no
                more lost data.
              </p>
              <div class="testimonial-footer">
                <span class="avatar" aria-hidden="true"><img src="images/client3.png" alt="client 3"></span>
                <div>
                  <p class="testimonial-name">Joseph R.</p>
                  <p class="testimonial-title">Treasurer, Professional Group</p>
                </div>
              </div>
            </article>

            <article class="testimonial-card">
              <div class="quotes">
                <span class="quote-icon" aria-hidden="true">&ldquo;</span>
                <span class="quote-icon" aria-hidden="true">&rdquo;</span>
              </div>
              <p class="testimonial-text">
                Setting up our organization took less than ten minutes.
                Adding members and assigning roles was straightforward.
                Exactly what a small association needs.
              </p>
              <div class="testimonial-footer">
                <span class="avatar" aria-hidden="true"><img src="images/client4.png" alt="client 4"></span>
                <div>
                  <p class="testimonial-name">Adekemi K.</p>
                  <p class="testimonial-title">President, Alumni Network</p>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="testimonial-controls">
          <button
            class="control-btn"
            id="testimonialPrev"
            aria-label="Previous testimonial"
          >
            <iconify-icon icon="ooui:next-rtl"></iconify-icon>
          </button>
          <button
            class="control-btn"
            id="testimonialNext"
            aria-label="Next testimonial"
          >
            <iconify-icon icon="ooui:next-ltr"></iconify-icon>
          </button>
        </div>
      </div>
    </section>

    <!-- ============================= -->
    <!-- SECTION 6: CTA BLOCK -->
    <!-- ============================= -->
    <?php include("inc/cta.php")?>

    <!-- ============================= -->
    <!-- SECTION 7: SITE FOOTER -->
    <!-- ============================= -->
    <?php include("inc/footer.php") ?>

    <script>
      (function () {
        var track = document.getElementById("testimonialsTrack");
        var prevBtn = document.getElementById("testimonialPrev");
        var nextBtn = document.getElementById("testimonialNext");

        if (!track || !prevBtn || !nextBtn) return;

        var cards = Array.prototype.slice.call(track.children);
        var index = 0;

        function visibleCount() {
          if (window.innerWidth <= 768) return 1;
          if (window.innerWidth <= 1024) return 2;
          return 3;
        }

        function maxIndex() {
          return Math.max(0, cards.length - visibleCount());
        }

        function update() {
          var cardRect = cards[0].getBoundingClientRect();
          var gap =
            parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) ||
            0;
          var offset = index * (cardRect.width + gap);
          track.style.transform = "translateX(-" + offset + "px)";

          prevBtn.disabled = index === 0;
          nextBtn.disabled = index >= maxIndex();
        }

        prevBtn.addEventListener("click", function () {
          index = Math.max(0, index - 1);
          update();
        });

        nextBtn.addEventListener("click", function () {
          index = Math.min(maxIndex(), index + 1);
          update();
        });

        window.addEventListener("resize", function () {
          index = Math.min(index, maxIndex());
          update();
        });

        update();
      })();
    </script>
    <script src="js/mobilemenu.js"></script>
    <script src="js/fixedtop.js"></script>
    <script src="js/fadeAnimate.js"></script>
    <script src="js/video-modal.js"></script>
  </body>
</html>