<aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
          <img
            src="../images/brand-logo-dark.png"
            alt="Brand Logo"
            style="width: 30%; height: auto"
          />
        </div>

        <ul class="sidebar-menu">
          <!-- Dashboard (Single Link) -->
          <li class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link active">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-cube"></i>
                <span>Dashboard</span>
              </div>
            </a>
          </li>

          <?php if (admin_has_permission('members_view')): ?>
          <!-- Membership (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-users"></i>
                <span>Membership</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="member-directory.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-users"></i>
                    <span>Membership Directory</span>
                  </div>
                </a>
              </li>
              <?php if (admin_has_permission('members_manage')): ?>
              <li>
                <a href="titles-hierarchy.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Titles & Hierarchy</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="zones.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Zones & Sub-Zones</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="suspension.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-gavel"></i>
                    <span>Suspension & Reinsta..</span>
                  </div>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('admission')): ?>
          <!-- Admissions (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Admissions</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="admission-management.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>All Admissions</span>
                  </div>
                </a>
              </li>
              <?php if (admin_has_permission('admin_only')): ?>
              <li>
                <a href="portal-settings.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Portal Settings</span>
                  </div>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('cbt_management')): ?>
          <!-- CBT Management (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-file-lines"></i>
                <span>CBT Management</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="cbt-applicants.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Applicant</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="cbt-questions.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Question</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="cbt-results.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Result</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('finance')): ?>
          <!-- Financial (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-chart-column"></i>
                <span>Financial</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="financial.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Financial Dashboard</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('attendance')): ?>
          <!-- Attendance (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Attendance</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="attendance.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Attendance Manager</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('events')): ?>
          <!-- Events -->
          <li class="sidebar-item">
            <a href="events.php" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Events</span>
              </div>
            </a>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('document')): ?>
          <!-- Document (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-folder-open"></i>
                <span>Document</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="upload-document.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-folder-plus"></i>
                    <span>Add Files & Documents</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="documents.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-folder"></i>
                    <span>Document Manager</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('user_control')): ?>
          <!-- Administration (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Administration</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="user-controls.php" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>User Control</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <?php if (admin_has_permission('admin_only')): ?>
          <!-- Communication (Dropdown) -->
          <li class="sidebar-item dropdown">
            <a href="javascript:void(0)" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-comment-dots"></i>
                <span>Communication</span>
              </div>
              <i class="fa-solid fa-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
              <li>
                <a href="#" class="sidebar-link">
                  <div class="sidebar-link-content">
                    <i class="fa-solid fa-comments"></i>
                    <span>All Channel</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>

          <!-- Org Settings (Single Link) -->
          <li class="sidebar-item">
            <a href="#" class="sidebar-link">
              <div class="sidebar-link-content">
                <i class="fa-solid fa-gear"></i>
                <span>Org Settings</span>
              </div>
            </a>
          </li>
          <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
          <a href="logout.php" class="sidebar-link">
            <div class="sidebar-link-content">
              <i class="fa-solid fa-arrow-right-from-bracket"></i>
              <span>Logout</span>
            </div>
          </a>
        </div>
      </aside>