<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$admissionId = (int) ($_SESSION['applicant_admission_id'] ?? 0);
$examId = (int) ($_SESSION['applicant_exam_id'] ?? 0);
if ($admissionId < 1 || $examId < 1) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Enter your CBT access code before starting the assessment.'));
    exit;
}

$statement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.status, a.exam_expires_at, e.id AS exam_id, e.title, e.duration_minutes FROM admissions a INNER JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id WHERE a.id = ? AND e.id = ? AND a.org_id IS NOT NULL AND a.status = 'under_review' LIMIT 1");
if (!$statement) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($statement, 'ii', $admissionId, $examId);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$assessment = $result ? mysqli_fetch_assoc($result) : null;
$assessmentExpiresAt = $assessment ? strtotime($assessment['exam_expires_at'] . ' UTC') : 0;
if (!$assessment || empty($assessment['exam_expires_at']) || $assessmentExpiresAt <= time()) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This assessment is no longer available.'));
    exit;
}

$questionStatement = mysqli_prepare($conn, 'SELECT id, question_text, option_a, option_b, option_c, option_d, option_e FROM cbt_questions WHERE exam_id = ? ORDER BY id');
mysqli_stmt_bind_param($questionStatement, 'i', $examId);
mysqli_stmt_execute($questionStatement);
$questionResult = mysqli_stmt_get_result($questionStatement);
$questions = [];
if ($questionResult) {
    while ($question = mysqli_fetch_assoc($questionResult)) {
        $questions[] = $question;
    }
}
if (!$questions) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This exam has no questions yet. Contact the administrator.'));
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($assessment['title']); ?> - Associa8</title>
    <link rel="shortcut icon" href="images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css" />
    <style>
      .assessment-page { min-height: 100vh; padding: 7rem 1.25rem 3rem; background: #f8f9fc; user-select: none; -webkit-user-select: none; }
      .assessment-shell { width: min(100%, 900px); margin: auto; }
      .assessment-header, .question-card { background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-md); box-shadow: var(--shadow-card); }
      .assessment-header { display: flex; justify-content: space-between; gap: 1rem; align-items: center; padding: 1.5rem; margin-bottom: 1.25rem; }
      .assessment-header h1 { margin: 0; font-size: clamp(1.4rem, 3vw, 2rem); }
      .countdown { min-width: 120px; padding: .7rem 1rem; border-radius: var(--radius-pill); text-align: center; font-weight: 800; color: #fff; background: var(--color-dark-blue); }
      .question-card { display: none; padding: 1.5rem; margin-bottom: 1rem; }
      .question-card.is-active { display: block; }
      .question-card h2 { font-size: 1.05rem; margin-bottom: 1rem; }
      .answer-option { display: flex; gap: .65rem; align-items: flex-start; padding: .75rem; margin-top: .5rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); cursor: pointer; }
      .answer-option:hover { border-color: var(--color-accent); background: var(--color-accent-light); }
      .quiz-progress { margin: 0 0 1rem; color: var(--color-text-muted); font-size: .9rem; font-weight: 700; }
      .quiz-navigation { display: flex; justify-content: space-between; gap: .75rem; }
      .quiz-navigation button { min-width: 130px; border: 0; cursor: pointer; }
      .submit-assessment { margin-left: auto; }
      @media (max-width: 600px) { .assessment-header { align-items: flex-start; flex-direction: column; } .countdown { width: 100%; } }
    </style>
  </head>
  <body>
    <main class="assessment-page">
      <div class="assessment-shell">
        <header class="assessment-header">
          <div>
            <p style="margin: 0 0 .35rem; color: var(--color-text-muted);">Associa8 CBT assessment</p>
            <h1><?php echo htmlspecialchars($assessment['title']); ?></h1>
          </div>
          <div class="countdown" id="countdown" aria-live="polite">--:--</div>
        </header>
        <form action="proc-submit-cbt.php" method="POST" id="assessmentForm">
          <div class="quiz-progress" id="quizProgress" aria-live="polite"></div>
          <?php foreach ($questions as $index => $question): ?>
            <section class="question-card<?php echo $index === 0 ? ' is-active' : ''; ?>" data-question-index="<?php echo $index; ?>">
              <h2><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?></h2>
              <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $option => $column): ?>
                <?php if ($question[$column] !== null && $question[$column] !== ''): ?>
                  <label class="answer-option">
                    <input type="radio" name="answers[<?php echo (int) $question['id']; ?>]" value="<?php echo $option; ?>" />
                    <span><strong><?php echo $option; ?>.</strong> <?php echo htmlspecialchars($question[$column]); ?></span>
                  </label>
                <?php endif; ?>
              <?php endforeach; ?>
            </section>
          <?php endforeach; ?>
          <div class="quiz-navigation">
            <button class="btn btn-outline" type="button" id="previousQuestion">Previous</button>
            <button class="btn btn-primary" type="button" id="nextQuestion">Next</button>
            <button class="btn btn-primary submit-assessment" type="submit" id="submitAssessment">Submit assessment</button>
          </div>
        </form>
      </div>
    </main>
    <script>
      const expiryTimestamp = <?php echo $assessmentExpiresAt; ?> * 1000;
      const countdown = document.getElementById("countdown");
      const form = document.getElementById("assessmentForm");
      const questionCards = [...document.querySelectorAll(".question-card")];
      const progress = document.getElementById("quizProgress");
      const previousButton = document.getElementById("previousQuestion");
      const nextButton = document.getElementById("nextQuestion");
      const submitButton = document.getElementById("submitAssessment");
      const questionStorageKey = "associa8-cbt-question-<?php echo (int) $admissionId; ?>";
      const answersStorageKey = "associa8-cbt-answers-<?php echo (int) $admissionId; ?>";
      let savedAnswers = {};
      try {
        savedAnswers = JSON.parse(localStorage.getItem(answersStorageKey) || "{}");
      } catch (error) {
        savedAnswers = {};
      }
      let currentQuestion = Number.parseInt(localStorage.getItem(questionStorageKey) || "0", 10);
      if (!Number.isInteger(currentQuestion)) currentQuestion = 0;
      let submitted = false;
      document.querySelectorAll('input[type="radio"][name^="answers["]').forEach((input) => {
        const answerId = input.name.match(/answers\[(\d+)\]/)?.[1];
        if (answerId && savedAnswers[answerId] === input.value) input.checked = true;
        input.addEventListener("change", () => {
          if (!answerId) return;
          savedAnswers[answerId] = input.value;
          localStorage.setItem(answersStorageKey, JSON.stringify(savedAnswers));
        });
      });
      function showQuestion(index) {
        currentQuestion = Math.max(0, Math.min(index, questionCards.length - 1));
        localStorage.setItem(questionStorageKey, String(currentQuestion));
        questionCards.forEach((card, cardIndex) => card.classList.toggle("is-active", cardIndex === currentQuestion));
        progress.textContent = `Question ${currentQuestion + 1} of ${questionCards.length}`;
        previousButton.hidden = currentQuestion === 0;
        nextButton.hidden = currentQuestion === questionCards.length - 1;
        submitButton.hidden = currentQuestion !== questionCards.length - 1;
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
      previousButton.addEventListener("click", () => showQuestion(currentQuestion - 1));
      nextButton.addEventListener("click", () => showQuestion(currentQuestion + 1));
      form.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" || event.target.matches("textarea, button")) return;
        event.preventDefault();
        if (currentQuestion < questionCards.length - 1) {
          showQuestion(currentQuestion + 1);
        } else {
          form.requestSubmit(submitButton);
        }
      });

      document.addEventListener("contextmenu", (event) => event.preventDefault());
      document.addEventListener("copy", (event) => event.preventDefault());
      document.addEventListener("cut", (event) => event.preventDefault());
      document.addEventListener("dragstart", (event) => event.preventDefault());
      document.addEventListener("visibilitychange", () => {
        if (document.hidden && !submitted) {
          document.title = "Return to your assessment - Associa8";
          progress.textContent = "Please return to the assessment. Your time is still running.";
        } else if (!submitted) {
          document.title = <?php echo json_encode($assessment['title'] . ' - Associa8'); ?>;
          progress.textContent = `Question ${currentQuestion + 1} of ${questionCards.length}`;
        }
      });
      form.addEventListener("submit", (event) => {
        if (!submitted && !window.confirm("Are you sure you want to submit your assessment? You cannot change your answers after submitting.")) {
          event.preventDefault();
          return;
        }
        submitted = true;
        localStorage.removeItem(questionStorageKey);
        localStorage.removeItem(answersStorageKey);
      });
      function updateCountdown() {
        const remaining = Math.max(0, Math.ceil((expiryTimestamp - Date.now()) / 1000));
        const hours = String(Math.floor(remaining / 3600)).padStart(2, "0");
        const minutes = String(Math.floor((remaining % 3600) / 60)).padStart(2, "0");
        const seconds = String(remaining % 60).padStart(2, "0");
        countdown.textContent = (remaining >= 3600 ? hours + ":" : "") + minutes + ":" + seconds;
        if (remaining === 0 && !submitted) {
          submitted = true;
          localStorage.removeItem(questionStorageKey);
          localStorage.removeItem(answersStorageKey);
          form.submit();
        }
      }
      showQuestion(currentQuestion);
      updateCountdown();
      setInterval(updateCountdown, 1000);
    </script>
  </body>
</html>
