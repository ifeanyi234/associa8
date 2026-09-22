<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
$examSql = "SELECT id, title FROM cbt_exams WHERE status <> 'closed'";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $examSql .= " AND org_id = " . $orgId;
}
$examsResult = mysqli_query($conn, $examSql . " ORDER BY created_at DESC");
$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? '';
$selectedExamId = (int) ($_GET['exam_id'] ?? 0);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add CBT Question - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay">
</div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button">
              <i class="fa-solid fa-bars">
</i>
            </button>
            <h1 class="page-title">Add CBT Question</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Create Question</h2>
              <p class="page-subtitle">Add a multiple-choice question to an exam.</p>
            </div>
            <a href="cbt-questions.php" class="btn-outline-primary">
              <i class="fa-solid fa-arrow-left">
</i> Back to questions
            </a>
          </div>
          <?php if ($message !== ''): ?>
            <div class="inline-form-message <?php echo $status === 'success' ? 'success' : 'error'; ?>" role="status">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>
          <section class="dashboard-card structure-form-card">
            <form action="proc-add-cbt-question.php" method="POST" class="structure-form">
              <div class="form-group">
                <label class="form-label" for="examId">Exam</label>
                <select class="form-select" id="examId" name="exam_id" required>
                  <option value="" selected disabled>Select exam</option>
                  <?php if ($examsResult): ?>
                    <?php while ($exam = mysqli_fetch_assoc($examsResult)): ?>
                      <option value="<?php echo (int) $exam['id']; ?>" <?php echo $selectedExamId === (int) $exam['id'] ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($exam['title']); ?>
</option>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="question">Question</label>
                <textarea class="form-control" id="question" name="question_text" rows="4" required>
</textarea>
              </div>
              <div class="form-row-2col">
                <?php foreach (['a','b','c','d','e'] as $option): ?>
                  <div class="form-group">
                    <label class="form-label" for="option<?php echo strtoupper($option); ?>">Option <?php echo strtoupper($option); ?>
<?php echo $option === 'e' ? ' (optional)' : ''; ?>
</label>
                    <input class="form-control" id="option<?php echo strtoupper($option); ?>" name="option_<?php echo $option; ?>" <?php echo $option === 'e' ? '' : 'required'; ?> />
                  </div>
                <?php endforeach; ?>
                <div class="form-group">
                  <label class="form-label" for="correct">Correct option</label>
                  <select class="form-select" id="correct" name="correct_option" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                  </select>
                </div>
              </div>
              <div class="structure-form-actions">
                <a href="cbt-questions.php" class="btn-action-dark-outline">Cancel</a>
                <button class="btn-navy-filled" type="submit">
<i class="fa-solid fa-check">
</i> Save question</button>
              </div>
            </form>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js">
</script>
    <script src="../js/preloader.js">
</script>
    <style>
      .inline-form-message {
        margin-bottom: 1.25rem;
        padding: .85rem 1rem;
        border-radius: var(--radius-sm);
        font-weight: 700;
      }
      .inline-form-message.success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
      }
      .inline-form-message.error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
      }
    </style>
  </body>
</html>

