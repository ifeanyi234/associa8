<?php $currentMemberPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')); ?>
<aside class="admin-sidebar" id="adminSidebar">
        <button class="sidebar-toggle-btn" id="sidebarCloseBtn" type="button" aria-label="Close sidebar">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="sidebar-brand">
          <img
            src="../../images/brand-logo-dark.png"
            alt="Brand Logo"
            style="width: 30%; height: auto"
          />
        </div>
        <ul class="sidebar-menu">
          <li class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link <?php echo $currentMemberPage === 'dashboard.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'dashboard.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-solid fa-cube"></i> Dashboard
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="profile.php" class="sidebar-link <?php echo $currentMemberPage === 'profile.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'profile.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-solid fa-user"></i> Profile
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="attendance.php" class="sidebar-link <?php echo $currentMemberPage === 'attendance.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'attendance.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-solid fa-user-check"></i> Attendance
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="payment.php" class="sidebar-link <?php echo $currentMemberPage === 'payment.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'payment.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-solid fa-money-bill-wave"></i> Payment
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="events.php" class="sidebar-link <?php echo $currentMemberPage === 'events.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'events.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-regular fa-calendar"></i> Events
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="messages.php" class="sidebar-link <?php echo $currentMemberPage === 'messages.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'messages.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-regular fa-comment"></i> Messages
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="documents.php" class="sidebar-link <?php echo $currentMemberPage === 'documents.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'documents.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-regular fa-file-lines"></i> Document
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="settings.php" class="sidebar-link <?php echo $currentMemberPage === 'settings.php' ? 'active' : ''; ?>" <?php echo $currentMemberPage === 'settings.php' ? 'aria-current="page"' : ''; ?>>
              <span class="sidebar-link-content">
                <i class="fa-solid fa-gear"></i> Settings
              </span>
            </a>
          </li>
        </ul>
        <div class="sidebar-footer">
          <a href="../../logout.php" class="sidebar-link">
            <span class="sidebar-link-content">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </span>
          </a>
        </div>
      </aside>
