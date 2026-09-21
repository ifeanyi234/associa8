<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$questionResult = mysqli_query($conn, "SELECT q.id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.option_e, q.correct_option, e.title AS exam_title FROM cbt_questions q INNER JOIN cbt_exams e ON e.id = q.exam_id ORDER BY q.created_at DESC");

$questions = [];

if ($questionResult) { 
  while ($question = mysqli_fetch_assoc($questionResult)) {
    $questions[] = $question; 
  } 
}

$examCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM cbt_exams");
$examCount = $examCountResult ? (int) mysqli_fetch_assoc($examCountResult)['total'] : 0;

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CBT Questions - Associa8</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />

    <!-- Font Awesome Icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />

    <!-- Favicon -->
    <link
      rel="shortcut icon"
      href="../images/fav-logo.png"
      type="image/x-icon"
    />

    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <!-- Preloader -->
    <?php include('inc/preloader.php') ?>

    <div class="admin-layout">
      <!-- SIDEBAR NAVIGATION -->
      <?php include('inc/sidebar.php') ?>

      <!-- Mobile sidebar backdrop: tap it to close the sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        <!-- Top Navigation Header -->
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">CBT Management</h1>
          </div>

          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>

            <button class="notification-btn" aria-label="Notifications">
              <i class="fa-solid fa-bell"></i>
              <span class="notification-badge"></span>
            </button>

            <div class="admin-user-profile">
              <div class="avatar-badge">SA</div>
              <div class="user-info">
                <span class="user-name">Super Admin</span>
                <span class="user-role">Full Access</span>
              </div>
            </div>
          </div>
        </header>

        <!-- Dashboard Body Content -->
        <div class="dashboard-content">
          <!-- Page Main Action Header -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">CBT Questions</h2>
              <p class="page-subtitle">CBT Schedule & Onboarding</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
              <a href="add-cbt-question.php" class="btn-outline-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Add Questions</span>
              </a>
              <a href="add-cbt-exam.php" class="btn-outline-primary"><i class="fa-solid fa-file-circle-plus"></i><span>Add Exam</span></a>
              <button class="btn-outline-primary">
                <i class="fa-solid fa-file-export"></i>
                <span>Export Question</span>
              </button>
              <button class="btn-outline-primary">
                <i class="fa-solid fa-upload"></i>
                <span>Bulk Upload</span>
              </button>
            </div>
          </div>

          <!-- Summary Stat Cards Grid -->
          <section class="stats-grid mb-4">
  <div class="suspension-stat-card">
    <div class="suspension-stat-value"><?php echo count($questions); ?></div>
    <div class="suspension-stat-label">Application Review</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value"><?php echo $examCount; ?></div>
    <div class="suspension-stat-label">CBT Review</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value">14</div>
    <div class="suspension-stat-label">Onboarding</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value text-primary">9</div>
    <div class="suspension-stat-label">Approved</div>
  </div>
</section>

          <!-- Questions Container Card -->
          <div class="table-responsive-card" style="padding: 0; overflow: hidden;">
            <div style="background: #0f2744; color: #ffffff; padding: 16px 24px; font-weight: 600; font-size: 1.05rem;">
              Questions
            </div>

            <div style="padding: 24px;">
              <?php if ($questions): ?>
                <?php foreach ($questions as $index => $question): ?>
                  <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f2744; margin-bottom: 12px;">Question 
                      <?php echo $index + 1; ?> 
                      <small>(<?php echo htmlspecialchars($question['exam_title']); ?>)</small>
                    </h3>
                    <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                      <?php echo htmlspecialchars($question['question_text']); ?>
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem; color: #475569; margin-bottom: 20px;">
                      <?php foreach (['a', 'b', 'c', 'd', 'e'] as $option): ?>
                        <?php if ($question['option_' . $option] !== null && $question['option_' . $option] !== ''): ?><div>(<?php echo strtoupper($option); ?>) <?php echo htmlspecialchars($question['option_' . $option]); ?></div><?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                      <span style="background: #0f2744; color: #fff; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Ans: <?php echo htmlspecialchars($question['correct_option']); ?></span>
                      <form action="proc-delete-cbt-question.php" method="POST" onsubmit="return confirm('Delete this question?');">
                        <input type="hidden" name="question_id" value="<?php echo (int) $question['id']; ?>" />
                        <button type="submit" style="background: #ef4444; color: #fff; border: none; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Del</button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
              <div style="margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f2744; margin-bottom: 12px;">Question 1</h3>
                <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                  An insurance policy covering fire damage to stock pays 70% of the cost for the first $1000 and all the cost thereafter up to total of $7000, following a claim , the claimant had to pay an additional of $2000 to damage stock. how much was the stock cost?
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem; color: #475569; margin-bottom: 20px;">
                  <div>(A) . $9000</div>
                  <div>(B) . $9700</div>
                  <div>(C) . $9300</div>
                  <div>(D) . $8700</div>
                  <div>(E) . $8300</div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                  <span style="background: #0f2744; color: #fff; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                    Ans: C
                  </span>
                  <button type="button" style="background: #ef4444; color: #fff; border: none; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                    Del
                  </button>
                </div>
              </div>
              <?php endif; ?>

              <?php if (!$questions): ?>
              <!-- Question Item 2 -->
              <div style="margin-bottom: 12px;">
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f2744; margin-bottom: 12px;">Question 2</h3>
                <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                  An insurance policy covering fire damage to stock pays 70% of the cost for the first $1000 and all the cost thereafter up to total of $7000, following a claim , the claimant had to pay an additional of $2000 to damage stock. how much was the stock cost?
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem; color: #475569; margin-bottom: 20px;">
                  <div>(A) . $9000</div>
                  <div>(B) . $9700</div>
                  <div>(C) . $9300</div>
                  <div>(D) . $8700</div>
                  <div>(E) . $8300</div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                  <span style="background: #0f2744; color: #fff; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                    Ans: C
                  </span>
                  <button type="button" style="background: #ef4444; color: #fff; border: none; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                    Del
                  </button>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        if (!status || !window.AppModal) return;
        const success = status === "success";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "Question deleted" : "Question not deleted",
          body: params.get("msg") || "Please try again.",
          detail: success ? "The question was removed from the database." : "No question was removed.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script>
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");
      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        link.addEventListener("click", (e) => {
          e.preventDefault();
          const isOpen = item.classList.contains("open");

          dropdownItems.forEach((otherItem) => {
            if (otherItem !== item) {
              otherItem.classList.remove("open");
              const otherSub = otherItem.querySelector(".sidebar-submenu");
              if (otherSub) otherSub.style.maxHeight = null;
            }
          });

          if (!isOpen) {
            item.classList.add("open");
            submenu.style.maxHeight = submenu.scrollHeight + "px";
          } else {
            item.classList.remove("open");
            submenu.style.maxHeight = null;
          }
        });
      });

      const sidebarEl = document.getElementById("adminSidebar");
      const sidebarOverlay = document.getElementById("sidebarOverlay");

      function openSidebar() {
        sidebarEl.classList.add("open");
        sidebarOverlay.classList.add("active");
      }

      function closeSidebar() {
        sidebarEl.classList.remove("open");
        sidebarOverlay.classList.remove("active");
      }

      document.getElementById("sidebarToggle").addEventListener("click", () => {
        sidebarEl.classList.contains("open") ? closeSidebar() : openSidebar();
      });

      sidebarOverlay.addEventListener("click", closeSidebar);

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
      });

      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => {
        const parentItem = link.closest(".sidebar-item");
        const isDropdownToggle = parentItem && parentItem.classList.contains("dropdown") && link === parentItem.querySelector(":scope > .sidebar-link");
        if (!isDropdownToggle) {
          link.addEventListener("click", closeSidebar);
        }
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>