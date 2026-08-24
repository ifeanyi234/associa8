<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Associa8 - About Us</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <!-- Iconify for Icons -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="css/styles.css" />
  </head>
  <body>
    <!-- SECTION 1: HERO -->
    <header class="hero hero-about">
      <div class="hero-bg hero-bg-about"></div>

      <!-- Full-width wrapper for the dynamic fixed blur header -->
      <?php include("inc/navbar.php")?>

      <div class="container">
        <div class="hero-about-content">
          <h1>About</h1>
        </div>
      </div>
    </header>
    <?php include("inc/mobile-nav.php") ?>
    <!-- SECTION 2: ABOUT ASSOCIA8 -->
    <section class="about-intro">
      <div class="container about-grid">
        <div class="about-images">
          <!-- <div
            class="img-back"
            style="background-image: url(&quot;images/about1.jpg&quot;)"
          ></div>
          <div
            class="img-front"
            style="background-image: url(&quot;images/about2.png&quot;)"
          ></div> -->
          <img src="images/abot pics.png" alt="about picture">
        </div>
        <div class="about-text">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            About Associa8
          </div>
          <h2>
            Empowering Organizations Through Smarter Membership Management
          </h2>
          <p class="section-description">
            Associa8 is an all-in-one membership management platform built to
            help organizations simplify administration, strengthen member
            engagement, and operate more efficiently. Whether you're managing a
            professional association, church, alumni network, non profit, club,
            cooperative, or community group, Associa8 provides the tools you
            need to manage members, finances, events, attendance, and
            communication from one secure platform.
          </p>
          <p class="section-description">
            Our goal is to eliminate administrative complexity through
            technology, enabling organizations to focus less on paperwork and
            more on building meaningful relationships, growing their
            communities, and delivering greater value to their members.
          </p>
        </div>
      </div>
    </section>

    <!-- SECTION 3: VALUE PROPOSITION -->
    <section class="value-prop">
      <div class="container">
        <div class="section-header">
          <div class="eyebrow center pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Our Value Preposition
            <span class="pointer">
              <span class="block"></span>
              <hr />
            </span>
          </div>
          <h2>Flexible Pricing for Every Organization</h2>
        </div>

        <div class="overlap-container">
          <!-- Overlapping Dark Card -->
          <div class="dark-box">
            <div class="val-item">
              <span class="icon-badge">
                <iconify-icon icon="icon-park-solid:protect"></iconify-icon>
              </span>
              <span>Security</span>
            </div>
            <div class="val-item">
              <span class="icon-badge">
                <iconify-icon icon="iconoir:star-solid"></iconify-icon>
              </span>
              <span>Innovation</span>
            </div>
            <div class="val-item">
              <span class="icon-badge">
                <iconify-icon icon="flowbite:clock-solid"></iconify-icon>
              </span>
              <span>Collaboration</span>
            </div>
            <div class="val-item">
              <span class="icon-badge">
                <iconify-icon icon="fa7-solid:handshake"></iconify-icon>
              </span>
              <span>Integrity</span>
            </div>
          </div>

          <!-- White Base Card -->
          <div class="white-card">
            <div class="card-col mission-col">
              <div class="col-header">
                <div class="col-icon">
                  <iconify-icon icon="lucide:shrink"></iconify-icon>
                </div>
                <h3>Mission</h3>
              </div>
              <p>
                To empower organizations with innovative technology that
                simplifies operations, strengthens member engagement, and
                fosters long-term growth.
              </p>
            </div>
            <div class="card-col vision-col">
              <div class="col-header">
                <div class="col-icon">
                  <iconify-icon icon="tabler:mountain-filled"></iconify-icon>
                </div>
                <h3>Vision</h3>
              </div>
              <p>
                To become the world's most trusted platform for managing
                communities, associations, and membership-based organizations.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 4: DEMOGRAPHICS & IMPACT -->
    <section class="impact">
      <div class="container impact-grid">
        <div class="demographics">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Who we serve
          </div>
          <h2>Built for Organizations That Bring People Together.</h2>

          <div class="progress-bars">
            <div class="progress-item">
              <div class="progress-labels">
                <span>Professional Associations</span>
                <span>97%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: 97%"></div>
              </div>
            </div>
            <div class="progress-item">
              <div class="progress-labels">
                <span>Educational Institute</span>
                <span>93%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: 93%"></div>
              </div>
            </div>
            <div class="progress-item">
              <div class="progress-labels">
                <span>Non Governmental Organizations</span>
                <span>90%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: 90%"></div>
              </div>
            </div>
            <div class="progress-item">
              <div class="progress-labels">
                <span>Community Organizations</span>
                <span>87%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: 87%"></div>
              </div>
            </div>
            <div class="progress-item">
              <div class="progress-labels">
                <span>Alumni Associations</span>
                <span>97%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: 97%"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="impact-stats">
          <div class="eyebrow pointer-holder">
            <span class="pointer">
              <hr />
              <span class="block"></span>
            </span>
            Our impact in numbers
          </div>
          <h2>Driving Measurable Success for Organizations Everywhere</h2>
          <div class="stats-grid">
            <div class="stat">
              <div class="stat-number">500</div>
              <div class="stat-label">Organization Empowered</div>
            </div>
            <div class="stat">
              <div class="stat-number">500</div>
              <div class="stat-label">Members Managed</div>
            </div>
            <div class="stat">
              <div class="stat-number">97%</div>
              <div class="stat-label">Customer Satisfaction Rate</div>
            </div>
            <div class="stat">
              <div class="stat-number">99.9%</div>
              <div class="stat-label">Platform Uptime</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 5: WHY CHOOSE US -->
    <section class="why-choose">
      <div class="split-left">
        <h2>Why<br />Organizations<br />Choose<br />Associa8</h2>
        <p>Everything You Need to Manage Your Organization in One Platform.</p>
      </div>
      <div class="split-right">
        <div class="steps-list">
          <div class="step-item">
            <div class="step-circle">01</div>
            <div class="step-content">
              <h3>Membership Management</h3>
              <p>
                Organize member records, automate registrations, renewals, and
                profile updates with ease.
              </p>
            </div>
          </div>
          <div class="step-item">
            <div class="step-circle">02</div>
            <div class="step-content">
              <h3>Event Management</h3>
              <p>
                Plan, promote, manage registrations, and track participation for
                conferences, seminars, and community events.
              </p>
            </div>
          </div>
          <div class="step-item">
            <div class="step-circle">03</div>
            <div class="step-content">
              <h3>Finance & Payment Management</h3>
              <p>
                Collect dues, process payments, issue receipts, and monitor
                financial activities from a centralized dashboard.
              </p>
            </div>
          </div>
          <div class="step-item">
            <div class="step-circle">04</div>
            <div class="step-content">
              <h3>Attendance Tracking</h3>
              <p>
                Record and monitor attendance for meetings, events, services,
                and other organizational activities in real time.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 6: FAQ -->
    <section class="faq-section">
      <div class="container faq-grid">
        <div class="faq-accordion">
          <div class="faq-item">
            <button class="faq-toggle">
              <span class="icon-plus">+</span>
              Who can use Associa8?
            </button>
            <div class="faq-content">
              <p>
                Associa8 is designed for membership-based organizations of all sizes. Whether you manage a professional association, church, nonprofit, alumni association, cooperative, club, educational institution, or community group, our platform provides the tools you need to streamline operations, engage members, and manage your organization efficiently.
              </p>
            </div>
          </div>
          <div class="faq-item">
            <button class="faq-toggle">
              <span class="icon-plus">+</span>
              Can I migrate from another platform?
            </button>
            <div class="faq-content">
              <p>
                Absolutely. We can help you migrate your existing member records, financial data, and other essential information from your current system to Associa8. Our onboarding team works with you to make the transition as smooth as possible while minimizing disruption to your organization.
              </p>
            </div>
          </div>
          <div class="faq-item">
            <button class="faq-toggle">
              <span class="icon-plus">+</span>
              How long does initial setup take?
            </button>
            <div class="faq-content">
              <p>
                Yes. Our dedicated support team is available to assist you with onboarding, platform setup, troubleshooting, and ongoing questions. We also provide helpful resources, guides, and documentation to ensure you get the most out of Associa8.
              </p>
            </div>
          </div>
          <div class="faq-item">
            <button class="faq-toggle">
              <span class="icon-plus">+</span>
              Is my organization's data secure?
            </button>
            <div class="faq-content">
              <p>
                Yes. Security is a top priority at Associa8. We use industry-standard security practices, encrypted data transmission, role-based access controls, and secure cloud infrastructure to help protect your organization's information. Regular backups and continuous monitoring ensure your data remains safe, available, and reliable.
              </p>
            </div>
          </div>
        </div>

        <div class="faq-text">
          <h2>Frequently Asked<br />Question</h2>
          <p class="section-description">
            Have questions? We've answered the most frequently asked questions
            to help you get started quickly and confidently.
          </p>
          <button class="btn btn-accent">Submit</button>
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
    <?php include("inc/footer.php")?>
    <script src="js/mobilemenu.js"></script>
    <script src="js/fixedtop.js"></script>
    <script src="js/accordion.js"></script>
    <script src="js/video-modal.js"></script>
  </body>
</html>
