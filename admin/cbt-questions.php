<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

$questionsSql = "SELECT q.id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.option_e, q.correct_option, q.image_path, e.id AS exam_id, e.title AS exam_title, e.status AS exam_status, (e.status = 'draft' AND assigned.cbt_exam_id IS NULL AND result_usage.exam_id IS NULL) AS can_delete FROM cbt_questions q INNER JOIN cbt_exams e ON e.id = q.exam_id LEFT JOIN (SELECT DISTINCT cbt_exam_id FROM admissions WHERE cbt_exam_id IS NOT NULL) assigned ON assigned.cbt_exam_id = e.id LEFT JOIN (SELECT DISTINCT exam_id FROM cbt_results) result_usage ON result_usage.exam_id = e.id";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $questionsSql .= " WHERE e.org_id = " . (int) $orgId;
}
$questionsSql .= " ORDER BY q.created_at DESC";
$questionResult = mysqli_query($conn, $questionsSql);

$questions = [];
if (!$questionResult) {
  error_log('CBT questions query failed: ' . mysqli_error($conn));
  http_response_code(500);
  exit('CBT questions are temporarily unavailable.');
}
while ($question = mysqli_fetch_assoc($questionResult)) {
  $questions[] = $question;
}
$questionsByExam = [];
foreach ($questions as $question) {
  $questionsByExam[(int) $question['exam_id']][] = $question;
}

$examCountSql = "SELECT COUNT(*) AS total FROM cbt_exams";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $examCountSql .= " WHERE org_id = " . (int) $orgId;
}
$examCountResult = mysqli_query($conn, $examCountSql);
if (!$examCountResult) {
  error_log('CBT exam count query failed: ' . mysqli_error($conn));
  http_response_code(500);
  exit('CBT exam statistics are temporarily unavailable.');
}
$examCount = (int) mysqli_fetch_assoc($examCountResult)['total'];

$examStatusSql = "SELECT status, COUNT(*) AS total FROM cbt_exams";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $examStatusSql .= " WHERE org_id = " . (int) $orgId;
}
$examStatusSql .= ' GROUP BY status';
$examStatusResult = mysqli_query($conn, $examStatusSql);
if (!$examStatusResult) {
  error_log('CBT exam status query failed: ' . mysqli_error($conn));
  http_response_code(500);
  exit('CBT exam statistics are temporarily unavailable.');
}
$examStatuses = ['draft' => 0, 'active' => 0, 'closed' => 0];
while ($examStatus = mysqli_fetch_assoc($examStatusResult)) {
  if (array_key_exists($examStatus['status'], $examStatuses)) {
    $examStatuses[$examStatus['status']] = (int) $examStatus['total'];
  }
}

$examsSql = "SELECT e.id, e.title, e.status, e.duration_minutes, e.pass_mark, COALESCE(question_stats.question_count, 0) AS question_count, (e.status = 'draft' AND COALESCE(question_stats.question_count, 0) > 0 AND question_stats.ready_question_count = question_stats.question_count AND assigned.cbt_exam_id IS NULL AND result_usage.exam_id IS NULL) AS can_activate FROM cbt_exams e LEFT JOIN (SELECT exam_id, COUNT(*) AS question_count, SUM(CASE WHEN question_text <> '' AND option_a <> '' AND option_b <> '' AND option_c <> '' AND option_d <> '' AND correct_option IN ('A','B','C','D','E') AND (correct_option <> 'E' OR option_e <> '') THEN 1 ELSE 0 END) AS ready_question_count FROM cbt_questions GROUP BY exam_id) question_stats ON question_stats.exam_id = e.id LEFT JOIN (SELECT DISTINCT cbt_exam_id FROM admissions WHERE cbt_exam_id IS NOT NULL) assigned ON assigned.cbt_exam_id = e.id LEFT JOIN (SELECT DISTINCT exam_id FROM cbt_results) result_usage ON result_usage.exam_id = e.id";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $examsSql .= " WHERE e.org_id = " . (int) $orgId;
}
$examsSql .= ' ORDER BY e.created_at DESC';
$examsResult = mysqli_query($conn, $examsSql);
if (!$examsResult) {
  error_log('CBT exams query failed: ' . mysqli_error($conn));
  http_response_code(500);
  exit('CBT exams are temporarily unavailable.');
}
$exams = [];
while ($exam = mysqli_fetch_assoc($examsResult)) {
  $exams[] = $exam;
}

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
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
    <style>
      .admin-main { min-width: 0; max-width: 100%; overflow-x: hidden; }
      .dashboard-content { width: 100%; max-width: 100%; min-width: 0; overflow-x: hidden; }
      .page-action-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
      .page-action-header > div:last-child { display: flex; gap: 10px; flex-wrap: wrap; max-width: 100%; }
      .page-action-header .btn-outline-primary, .page-action-header .btn-navy-filled { white-space: nowrap; }
      .stats-grid, .dashboard-card, .table-responsive-card, .cbt-tab-panel { max-width: 100%; overflow: hidden; }
      .cbt-tabs { display: flex; gap: .5rem; margin: 0 0 1rem; border-bottom: 1px solid #dbe3ed; flex-wrap: wrap; }
      .cbt-tab { border: 0; border-bottom: 3px solid transparent; background: transparent; color: #64748b; padding: .85rem 1rem; font: inherit; font-weight: 700; cursor: pointer; }
      .cbt-tab[aria-selected="true"] { color: #0f2744; border-bottom-color: #0f2744; }
      .cbt-tab-panel[hidden] { display: none; }
      .exam-list { display: flex; flex-direction: column; gap: 0; width: 100%; }
      .exam-list-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #e2e8f0;
        width: 100%;
        max-width: 100%;
        overflow-wrap: anywhere;
      }
      .exam-list-content { min-width: 0; max-width: 100%; }
      .exam-list-content strong { display: block; word-break: break-word; overflow-wrap: anywhere; }
      .exam-list-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        min-width: 0;
        max-width: 100%;
      }
      .exam-list-actions > * { max-width: 100%; }
      .exam-list-actions .page-subtitle { white-space: normal; max-width: 220px; }
      @media (max-width: 768px) {
        .exam-list-item {
          grid-template-columns: 1fr;
          gap: 12px;
        }
        .exam-list-actions {
          justify-content: flex-start;
        }
      }
      .exam-accordion { display: flex; flex-direction: column; gap: 12px; }
      .exam-accordion-item { border: 1px solid #dfe7f1; border-radius: 14px; background: #fff; overflow: hidden; }
      .exam-accordion-item summary {
        list-style: none;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 16px 18px;
        cursor: pointer;
        font-weight: 700;
        color: #0f2744;
        background: #f8fafc;
      }
      .exam-accordion-item summary::-webkit-details-marker { display: none; }
      .exam-accordion-count {
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
      }
      .exam-accordion-item summary::after {
        content: "+";
        margin-left: auto;
        font-size: 1.4rem;
        line-height: 1;
        color: #0f2744;
      }
      .exam-accordion-item[open] summary::after { content: "−"; }
      .exam-accordion-content {
        padding: 18px;
        border-top: 1px solid #e2e8f0;
      }
      .exam-accordion-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
    </style>
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
              <p class="page-subtitle">Manage exam questions and review CBT exam status.</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
              <a href="add-cbt-question.php" class="btn-outline-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Add Questions</span>
              </a>
              <a href="add-cbt-exam.php" class="btn-outline-primary"><i class="fa-solid fa-file-circle-plus"></i><span>Add Exam</span></a>
              <a href="proc-download-cbt-template.php" class="btn-outline-primary">
                <i class="fa-solid fa-download"></i>
                <span>Download Template</span>
              </a>
              <button class="btn-outline-primary">
                <i class="fa-solid fa-file-export"></i>
                <span>Export Question</span>
              </button>
              <a href="cbt-bulk-upload.php" class="btn-outline-primary">
                <i class="fa-solid fa-upload"></i>
                <span>Bulk Upload</span>
              </a>
            </div>
          </div>

          <!-- Summary Stat Cards Grid -->
          <section class="stats-grid mb-4">
  <div class="suspension-stat-card">
    <div class="suspension-stat-value"><?php echo count($questions); ?></div>
    <div class="suspension-stat-label">Total Questions</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value"><?php echo $examCount; ?></div>
    <div class="suspension-stat-label">Total Exams</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value"><?php echo $examStatuses['active']; ?></div>
    <div class="suspension-stat-label">Active Exams</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value text-primary"><?php echo $examStatuses['draft']; ?></div>
    <div class="suspension-stat-label">Draft Exams</div>
  </div>
</section>

<div class="cbt-tabs" role="tablist" aria-label="CBT management sections">
  <button class="cbt-tab" id="examsTab" type="button" role="tab" aria-selected="true" aria-controls="examsPanel" tabindex="0">Exams</button>
  <button class="cbt-tab" id="questionsTab" type="button" role="tab" aria-selected="false" aria-controls="questionsPanel" tabindex="-1">Questions</button>
</div>
<section class="cbt-tab-panel" id="examsPanel" role="tabpanel" aria-labelledby="examsTab">
<section class="dashboard-card mb-4">
  <h2 class="page-title-main" style="font-size: 1.1rem;">Exams</h2>
  <p class="page-subtitle">Exams <?php admin_info_tip('Exams begin as drafts. Add and review complete questions before activating. Once active, questions cannot be changed.', 'Exam status help'); ?></p>
  <?php if ($exams): ?>
    <div class="exam-list">
      <?php foreach ($exams as $exam): ?>
        <div class="exam-list-item">
          <div class="exam-list-content">
            <strong><?php echo htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <div><?php echo htmlspecialchars(ucfirst($exam['status']), ENT_QUOTES, 'UTF-8'); ?> · <?php echo (int) $exam['question_count']; ?> questions · <?php echo (int) $exam['duration_minutes']; ?> minutes · Pass mark <?php echo (int) $exam['pass_mark']; ?>%</div>
          </div>
          <div class="exam-list-actions">
            <?php if ($exam['status'] === 'draft'): ?>
              <a class="btn-outline-primary" href="add-cbt-question.php?exam_id=<?php echo (int) $exam['id']; ?>">Add question</a>
              <?php if ((int) $exam['can_activate'] === 1): ?>
                <a class="btn-navy-filled" href="cbt-exam-preview.php?exam_id=<?php echo (int) $exam['id']; ?>">Review and activate</a>
              <?php else: ?>
                <span class="page-subtitle">Needs a complete question <?php admin_info_tip('The exam needs at least one complete question before it can be activated. Activation locks the question set.', 'Activation requirements help'); ?></span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="zone-empty-state">No CBT exams have been created yet.</p>
  <?php endif; ?>
</section>
</section>

<section class="cbt-tab-panel" id="questionsPanel" role="tabpanel" aria-labelledby="questionsTab" hidden>
<!-- Questions Container Card -->
          <div class="table-responsive-card" style="padding: 0; overflow: hidden;">
            <div style="background: #0f2744; color: #ffffff; padding: 16px 24px; font-weight: 600; font-size: 1.05rem;">
              Questions
            </div>

            <div style="padding: 24px;">
              <?php if ($questionsByExam): ?>
                <div class="exam-accordion">
                  <?php foreach ($questionsByExam as $examId => $examQuestions): ?>
                    <details class="exam-accordion-item" <?php echo $examId === array_key_first($questionsByExam) ? 'open' : ''; ?>>
                      <summary>
                        <span><?php echo htmlspecialchars($examQuestions[0]['exam_title'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="exam-accordion-count">(<?php echo count($examQuestions); ?> questions)</span>
                      </summary>
                      <div class="exam-accordion-content">
                        <?php foreach ($examQuestions as $index => $question): ?>
                          <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f2744; margin-bottom: 12px;">Question <?php echo $index + 1; ?></h4>
                            <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                              <?php echo htmlspecialchars($question['question_text'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <?php if (!empty($question['image_path'])): ?>
                              <img src="<?php echo htmlspecialchars($question['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Question image" style="display:block;max-width:100%;height:auto;margin:0 0 16px;border-radius:8px;" />
                            <?php endif; ?>
                            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem; color: #475569; margin-bottom: 20px;">
                              <?php foreach (['a', 'b', 'c', 'd', 'e'] as $option): ?>
                                <?php if ($question['option_' . $option] !== null && $question['option_' . $option] !== ''): ?><div>(<?php echo strtoupper($option); ?>) <?php echo htmlspecialchars($question['option_' . $option], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                              <?php endforeach; ?>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                              <span style="background: #0f2744; color: #fff; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">Ans: <?php echo htmlspecialchars($question['correct_option'], ENT_QUOTES, 'UTF-8'); ?></span>
                              <?php if ((int) $question['can_delete'] === 1): ?>
                                <form action="proc-delete-cbt-question.php" method="POST" data-app-modal-confirm="Delete this question? This cannot be undone." data-confirm-title="Delete question" data-confirm-detail="This action cannot be undone.">
                                  <input type="hidden" name="question_id" value="<?php echo (int) $question['id']; ?>" />
                                  <button type="submit" style="background: #ef4444; color: #fff; border: none; padding: 6px 16px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Delete</button>
                                </form>
                              <?php else: ?>
                                <span class="badge-pill status-<?php echo htmlspecialchars($question['exam_status'], ENT_QUOTES, 'UTF-8'); ?>">Question locked because this exam is already in use.</span>
                              <?php endif; ?>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </details>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="zone-empty-state">No CBT questions have been added yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </section>
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
        const examCreated = params.get("action") === "exam_created";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? (examCreated ? "Draft exam created" : "Action completed") : "Action not completed",
          body: examCreated && success ? "" : (params.get("msg") || "Please try again."),
          detail: success ? "" : "No CBT change was saved.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script>
      const cbtTabs = [...document.querySelectorAll(".cbt-tab")];
      cbtTabs.forEach((tab) => {
        tab.addEventListener("click", () => {
          cbtTabs.forEach((candidate) => {
            const selected = candidate === tab;
            candidate.setAttribute("aria-selected", String(selected));
            candidate.tabIndex = selected ? 0 : -1;
            document.getElementById(candidate.getAttribute("aria-controls")).hidden = !selected;
          });
        });
        tab.addEventListener("keydown", (event) => {
          if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
          event.preventDefault();
          const nextIndex = event.key === "Home" ? 0
            : event.key === "End" ? cbtTabs.length - 1
            : (cbtTabs.indexOf(tab) + (event.key === "ArrowRight" ? 1 : cbtTabs.length - 1)) % cbtTabs.length;
          cbtTabs[nextIndex].focus();
          cbtTabs[nextIndex].click();
        });
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