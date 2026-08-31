<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-brand">
      <a href="#" class="logo">
        <img src="images/brand-logo.png" alt="Brand logo" />
      </a>
      <p>
        An all-in-one platform that helps associations manage members,
        finances, attendance, and communication from a single secure
        dashboard.
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
          
        />
        <button type="submit" class="btn btn-accent">Submit</button>
      </form>
    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <p>Copyright &copy; 2026 Associa8. All rights reserved</p>
      <div class="social-icons">
        <a href="#" class="social-icon" aria-label="Facebook"><iconify-icon icon="mdi:facebook"></iconify-icon></a>
        <a href="#" class="social-icon" aria-label="Twitter"><iconify-icon icon="mdi:twitter"></iconify-icon></a>
        <a href="#" class="social-icon" aria-label="Instagram"><iconify-icon icon="mdi:instagram"></iconify-icon></a>
        <a href="#" class="social-icon" aria-label="LinkedIn"><iconify-icon icon="mdi:linkedin"></iconify-icon></a>
      </div>
    </div>
  </div>
</footer>

<div class="app-modal app-modal-info" id="appModal" aria-hidden="true">
  <div class="app-modal-backdrop" data-modal-close></div>
  <div class="app-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="appModalTitle" tabindex="-1">
    <button class="app-modal-close" type="button" data-modal-close aria-label="Close dialog">
      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
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