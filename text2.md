# Phase 8 — Admission → CBT → Member Pipeline

**Goal:** Applicants get notified at every status change, get scheduled for a CBT exam with a time-bound code sent to their email, take the assessment (adapted from the mentor-provided reference project), get scored automatically, and — on approval — get converted into a real `members` row.

---

## Step 1: Schema additions

- [ ] Add to `admissions`:
  - `exam_code VARCHAR(10) NULL`
  - `exam_scheduled_at DATETIME NULL`
  - `exam_expires_at DATETIME NULL`
  - `cbt_exam_id INT UNSIGNED NULL` (FK → `cbt_exams.id` — which exam this applicant is assigned to)
- [ ] Add `admission_id INT UNSIGNED NULL` to `notifications` (applicants aren't members yet, so the existing `member_id`-only notifications table can't reach them).
- [ ] Add `admission_id INT UNSIGNED NULL` to `cbt_results` (alongside the existing `member_id`), so a result can be tied to an applicant before they become a member.
- [ ] Add `cbt_scheduled` (and optionally `cbt_completed`) to the `admissions.status` ENUM — currently only `pending`, `under_review`, `approved`, `rejected`.

---

## Step 2: Status-change notifications

- [ ] Every place `admissions.status` changes (`proc-update-admission-status.php`, plus the new scheduling/submission steps below) should send an email to `admissions.email` at that moment.
- [ ] Use the existing `send_app_mail()` helper in `inc/mailer.php` (PHPMailer via SMTP, not the old `mail()` function) for every notification in this phase — `require_once __DIR__ . '/../inc/mailer.php';` then call `send_app_mail($toEmail, $toName, $subject, $bodyHtml)`.
- [ ] Statuses to notify on:
  - `pending` — application submitted
  - `under_review` — moved to CBT review stage
  - `cbt_scheduled` — exam scheduled, code sent
  - `approved`
  - `rejected`
- [ ] `inc/mailer.php` depends on `inc/mail-config.php` (gitignored, holds real SMTP credentials) — confirm that file exists locally before testing any of this step. If missing, copy `inc/mail-config.example.php` and fill in the real mailbox credentials (get these from the project owner, not from git).

---

## Step 3: Scheduling + exam code generation (admin side)

- [ ] Build `admin/schedule-cbt.php` — admin picks an applicant (status = `under_review`), assigns a `cbt_exams` row, sets `exam_scheduled_at`.
- [ ] Build `admin/proc-schedule-cbt.php`:
  - Generate a random exam code (mirror the mentor project's `participant.exam_code` pattern).
  - Set `exam_expires_at` (scheduled time + a window, matching the mentor project's `expire_date` logic).
  - Set `admissions.status = 'cbt_scheduled'`.
  - Send the code to the applicant's email.

---

## Step 4: Applicant-facing code entry (public, no login)

Adapted from the mentor project's `code-page.php` / `validate_code.php`.

- [ ] Build `cbt-code.php` (public) — applicant enters their email + exam code (adapt the 6-box code input UI from `code-page.php`).
- [ ] Build `proc-validate-cbt-code.php`:
  - Look up `admissions` by `email` + `exam_code`.
  - Reject if `NOW() > exam_expires_at` ("code expired").
  - Reject if already taken (check `cbt_results` for an existing row on this `admission_id`).
  - On success: start a session (`$_SESSION['applicant_admission_id']`), redirect to the assessment.

---

## Step 5: The assessment itself

Adapted from the mentor project's `test-question.php` + `end_test.php`.

- [ ] Build `cbt-assessment.php` — pull questions from `cbt_questions` for the assigned `cbt_exams.id`. (The mentor project pulled by `category`; yours should pull by `exam_id`, since that's how your own schema already links `cbt_questions.exam_id → cbt_exams.id`.)
- [ ] Reuse the timer/countdown logic from `test-question.php`, scoped to `cbt_exams.duration_minutes`.
- [ ] Build `proc-submit-cbt.php` (adapt `end_test.php`):
  - Score against `cbt_questions.correct_option`.
  - Compute `score` and `status` (`passed`/`failed`) based on `cbt_exams.pass_mark`.
  - Insert into `cbt_results` with `admission_id`, `exam_id`, `score`, `status`.
- [ ] On submit, update `admissions.status` to `under_review` (or the new `cbt_completed` status) so the admin knows a final decision is needed.

---

## Step 6: Approval → member conversion

ok

- [ ] In `proc-update-admission-status.php`, when status is set to `approved`:
  - Insert a new row into `members` using the admission's name/email/phone.
  - Generate a `member_code`.
  - Assign a default `zone_id`/`title_id`, or let the admin pick these at approval time via a small form.
  - Set a temp password (hashed) so the member can log in later via the Phase 5 member-login flow.
- [ ] Send the approval email with next steps / login info at this point.

---

## Step 7: Org-scope everything new

- [ ] Since this is built _after_ Phase 4, make sure every new table, column, and query carries `org_id` from the start — no retrofit needed this time.
- [ ] `cbt_exams`, `admissions`, and `cbt_results` already have `org_id`; just make sure every new proc file filters/inserts using `$_SESSION['org_id']` from day one.

---

## Build order recommendation

1. **Step 1 + Step 2** together first — small, and everything else depends on them.
2. **Steps 3–5** as one connected unit — the mentor's reference files map directly onto them, so build/test the full schedule → code → assessment → score loop in one pass.
3. **Step 6** last — the member-conversion logic, once the pipeline above is proven working.
