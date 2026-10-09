<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$examId = (int) ($_GET['exam_id'] ?? 0);
$adminRole = $_SESSION['admin_role'];
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : null;
if ($examId < 1) {
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Select a valid exam to review.'));
    exit;
}

$scopeSql = $adminRole === 'super_admin' ? '' : ' AND e.org_id = ?';
$examStatement = mysqli_prepare($conn, 'SELECT e.id, e.title, e.duration_minutes, e.pass_mark, e.status FROM cbt_exams e WHERE e.id = ?' . $scopeSql . ' LIMIT 1');
if (!$examStatement) {
    error_log('CBT exam preview query prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The CBT exam preview is temporarily unavailable.');
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($examStatement, 'i', $examId);
} else {
    mysqli_stmt_bind_param($examStatement, 'ii', $examId, $orgId);
}
if (!mysqli_stmt_execute($examStatement)) {
    error_log('CBT exam preview query failed: ' . mysqli_stmt_error($examStatement));
    http_response_code(500);
    exit('The CBT exam preview is temporarily unavailable.');
}
$examResult = mysqli_stmt_get_result($examStatement);
$exam = $examResult ? mysqli_fetch_assoc($examResult) : null;
if (!$exam) {
    if (!$examResult) {
        error_log('CBT exam preview query result failed: ' . mysqli_stmt_error($examStatement));
        http_response_code(500);
        exit('The CBT exam preview is temporarily unavailable.');
    }
    http_response_code(404);
    exit('The CBT exam was not found in your organization.');
}

$questionStatement = mysqli_prepare($conn, 'SELECT question_text, option_a, option_b, option_c, option_d, option_e, correct_option FROM cbt_questions WHERE exam_id = ? ORDER BY id');
if (!$questionStatement) {
    error_log('CBT exam preview questions prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The exam questions are temporarily unavailable.');
}
mysqli_stmt_bind_param($questionStatement, 'i', $examId);
if (!mysqli_stmt_execute($questionStatement)) {
    error_log('CBT exam preview questions query failed: ' . mysqli_stmt_error($questionStatement));
    http_response_code(500);
    exit('The exam questions are temporarily unavailable.');
}
$questionResult = mysqli_stmt_get_result($questionStatement);
if (!$questionResult) {
    error_log('CBT exam preview questions result failed: ' . mysqli_stmt_error($questionStatement));
    http_response_code(500);
    exit('The exam questions are temporarily unavailable.');
}
$questions = [];
$allQuestionsReady = true;
while ($question = mysqli_fetch_assoc($questionResult)) {
    $questionReady = trim($question['question_text']) !== ''
        && trim($question['option_a']) !== ''
        && trim($question['option_b']) !== ''
        && trim($question['option_c']) !== ''
        && trim($question['option_d']) !== ''
        && in_array($question['correct_option'], ['A', 'B', 'C', 'D', 'E'], true)
        && ($question['correct_option'] !== 'E' || trim((string) $question['option_e']) !== '');
    $question['is_ready'] = $questionReady;
    if (!$questionReady) {
        $allQuestionsReady = false;
    }
    $questions[] = $question;
}

$usageStatement = mysqli_prepare($conn, 'SELECT EXISTS(SELECT 1 FROM admissions WHERE cbt_exam_id = ?) AS has_applicants, EXISTS(SELECT 1 FROM cbt_results WHERE exam_id = ?) AS has_results');
if (!$usageStatement) {
    error_log('CBT exam preview usage prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The exam usage could not be checked.');
}
mysqli_stmt_bind_param($usageStatement, 'ii', $examId, $examId);
if (!mysqli_stmt_execute($usageStatement)) {
    error_log('CBT exam preview usage query failed: ' . mysqli_stmt_error($usageStatement));
    http_response_code(500);
    exit('The exam usage could not be checked.');
}
$usageResult = mysqli_stmt_get_result($usageStatement);
$usage = $usageResult ? mysqli_fetch_assoc($usageResult) : null;
if (!$usage) {
    error_log('CBT exam preview usage result failed: ' . mysqli_stmt_error($usageStatement));
    http_response_code(500);
    exit('The exam usage could not be checked.');
}
$canActivate = $exam['status'] === 'draft'
    && count($questions) > 0
    && $allQuestionsReady
    && (int) $usage['has_applicants'] === 0
    && (int) $usage['has_results'] === 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Review CBT Exam - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261008-infotips-3" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Review CBT Exam</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main"><?php echo htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="page-subtitle"><?php echo htmlspecialchars(ucfirst($exam['status']), ENT_QUOTES, 'UTF-8'); ?> · <?php echo count($questions); ?> questions · <?php echo (int) $exam['duration_minutes']; ?> minutes · Pass mark <?php echo (int) $exam['pass_mark']; ?>%</p>
              <p class="page-subtitle">Review every question and correct answer. Activating locks the questions and makes this exam available for scheduling.</p>
            </div>
            <a href="cbt-questions.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to exams</a>
          </div>

          <section class="dashboard-card">
            <?php if (!$questions): ?>
              <p class="zone-empty-state">No questions have been added. Add at least one complete question before activation.</p>
            <?php else: ?>
              <?php foreach ($questions as $index => $question): ?>
                <article style="padding:18px 0;border-bottom:1px solid #e2e8f0;">
                  <h3>Question <?php echo $index + 1; ?></h3>
                  <p><?php echo nl2br(htmlspecialchars($question['question_text'], ENT_QUOTES, 'UTF-8')); ?></p>
                  <ul>
                    <?php foreach (['a', 'b', 'c', 'd', 'e'] as $option): ?>
                      <?php $optionValue = $question['option_' . $option]; ?>
                      <?php if ($optionValue !== null && trim($optionValue) !== ''): ?>
                        <li>
                          (<?php echo strtoupper($option); ?>)
                          <?php echo htmlspecialchars($optionValue, ENT_QUOTES, 'UTF-8'); ?>
                          <?php if ($question['correct_option'] === strtoupper($option)): ?><strong> — Correct answer</strong><?php endif; ?>
                        </li>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </ul>
                  <?php if (!$question['is_ready']): ?>
                    <p role="alert">This question is incomplete. Fix it before activating the exam.</p>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
            <div class="structure-form-actions" style="margin-top:20px;">
              <a href="cbt-questions.php" class="btn-action-dark-outline">Back to exams</a>
              <?php if ($canActivate): ?>
                <form action="proc-activate-cbt-exam.php" method="POST" data-app-modal-confirm="Activate this exam? Applicants will see these questions, and you will not be able to change them." data-confirm-title="Activate this exam" data-confirm-detail="The exam questions will be locked after activation.">
                  <input type="hidden" name="exam_id" value="<?php echo (int) $exam['id']; ?>" />
                  <button class="btn-navy-filled" type="submit">Confirm and activate</button>
                </form>
              <?php elseif ($exam['status'] === 'draft' && $questions && $allQuestionsReady): ?>
                <p class="page-subtitle">This exam is already assigned or has results, so it cannot be activated.</p>
              <?php elseif ($exam['status'] === 'draft' && $questions && !$allQuestionsReady): ?>
                <p class="page-subtitle">Complete the questions marked above before activating this exam.</p>
              <?php endif; ?>
            </div>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
