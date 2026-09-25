<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$resultId = (int) ($_SESSION['applicant_result_id'] ?? 0);
if ($resultId < 1) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Your assessment result is no longer available in this session.'));
    exit;
}

$statement = mysqli_prepare($conn, 'SELECT r.score, r.status, r.taken_at, a.applicant_name, a.phone, e.title AS exam_title, e.pass_mark FROM cbt_results r INNER JOIN admissions a ON a.id = r.admission_id INNER JOIN cbt_exams e ON e.id = r.exam_id WHERE r.id = ? AND r.admission_id IS NOT NULL LIMIT 1');
if (!$statement) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The result service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($statement, 'i', $resultId);
mysqli_stmt_execute($statement);
$resultSet = mysqli_stmt_get_result($statement);
$result = $resultSet ? mysqli_fetch_assoc($resultSet) : null;
if (!$result) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment result could not be found.'));
    exit;
}
unset($_SESSION['applicant_result_id']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assessment Result - Associa8</title>
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css" />
    <style>
      .result-page { min-height: 100vh; display: grid; place-items: center; padding: 6rem 1.25rem 3rem; background: linear-gradient(135deg, #f8f9fc 0%, #eaf1ff 100%); }
      .result-card { width: min(100%, 560px); padding: 3rem; text-align: center; background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-card-strong); }
      .result-icon { width: 72px; height: 72px; margin: 0 auto 1.25rem; display: grid; place-items: center; border-radius: 50%; color: #fff; background: <?php echo $result['status'] === 'passed' ? '#15803d' : '#b91c1c'; ?>; font-size: 1.75rem; }
      .result-card h1 { margin-bottom: .5rem; }
      .result-card p { color: var(--color-text-muted); }
      .result-score { margin: 1.75rem 0; font-size: 3.5rem; line-height: 1; font-weight: 800; color: var(--color-dark-blue); }
      .result-status { display: inline-block; padding: .5rem 1rem; border-radius: var(--radius-pill); font-weight: 800; color: <?php echo $result['status'] === 'passed' ? '#166534' : '#991b1b'; ?>; background: <?php echo $result['status'] === 'passed' ? '#dcfce7' : '#fee2e2'; ?>; }
      .result-note { margin: 1.5rem 0 0; font-size: .9rem; }
    </style>
  </head>
  <body>
    <main class="result-page">
      <section class="result-card" aria-labelledby="resultHeading">
        <div class="result-icon" aria-hidden="true"><span><?php echo $result['status'] === 'passed' ? '&#10003;' : '&#33;'; ?></span></div>
        <h1 id="resultHeading">Assessment complete</h1>
        <p><?php echo htmlspecialchars($result['applicant_name']); ?>, your <?php echo htmlspecialchars($result['exam_title']); ?> result is ready.</p>
        <div class="result-score"><?php echo (int) $result['score']; ?>%</div>
        <div class="result-status"><?php echo htmlspecialchars(ucfirst($result['status'])); ?></div>
        <p class="result-note">Pass mark: <?php echo (int) $result['pass_mark']; ?>%. The admissions team will review your application and contact you about the next step.</p>
      </section>
    </main>
  </body>
</html>
