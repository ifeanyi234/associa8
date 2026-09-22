# Phase 8 — Admission → CBT → Member Pipeline

**Goal:** Applicants get notified at every status change, get scheduled for a CBT exam with a time-bound code sent to their email, take the assessment (adapted from the mentor-provided reference project), get scored automatically, and — on approval — get converted into a real `members` row.

---

## Step 1: Schema additions

- [x] Add to `admissions`:
  - `exam_code VARCHAR(10) NULL`
  - `exam_scheduled_at DATETIME NULL`
  - `exam_expires_at DATETIME NULL`
  - `cbt_exam_id INT UNSIGNED NULL` (FK → `cbt_exams.id` — which exam this applicant is assigned to)
- [x] Add `admission_id INT UNSIGNED NULL` to `notifications` (applicants aren't members yet, so the existing `member_id`-only notifications table can't reach them).
- [x] Add `admission_id INT UNSIGNED NULL` to `cbt_results` (alongside the existing `member_id`), so a result can be tied to an applicant before they become a member.
- [x] Add `cbt_scheduled` (and optionally `cbt_completed`) to the `admissions.status` ENUM — currently only `pending`, `under_review`, `approved`, `rejected`.

---

## Step 2: Status-change notifications

- [x] Every place `admissions.status` changes (`proc-update-admission-status.php`, plus the new scheduling/submission steps below) should send an email to `admissions.email` at that moment.
- [x] Use the existing `send_app_mail()` helper in `inc/mailer.php` (PHPMailer via SMTP, not the old `mail()` function) for every notification in this phase — `require_once __DIR__ . '/../inc/mailer.php';` then call `send_app_mail($toEmail, $toName, $subject, $bodyHtml)`.
- [ ] Statuses to notify on:
  - `pending` — application submitted
  - `under_review` — moved to CBT review stage
  - `cbt_scheduled` — exam scheduled, code sent
  - `approved`
  - `rejected`
- [x] `inc/mailer.php` depends on `inc/mail-config.php` (gitignored, holds real SMTP credentials) — confirmed present locally before testing this step.

---

## Step 3: Scheduling + exam code generation (admin side)

- [x] Build `admin/schedule-cbt.php` — admin picks an applicant (status = `under_review`), assigns a `cbt_exams` row, sets `exam_scheduled_at`.
- [x] Build `admin/proc-schedule-cbt.php`:
  - [x] Generate the access code when the applicant requests it from the assessment link.
  - [x] Set `exam_expires_at` (scheduled time + the assigned exam duration).
  - [x] Set `admissions.status = 'cbt_scheduled'`.
  - [x] Send the assessment link to the applicant when scheduling is complete.

---

## Step 4: Applicant-facing code entry (public, no login)

Adapted from the mentor project's `code-page.php` / `validate_code.php`.

- [x] Build `cbt-code.php` (public) — applicant enters their email first.
- [x] Build `proc-validate-cbt-email.php` — verify the scheduled email, generate the six-character code, email it, and redirect to code entry.
- [x] Build `cbt-code-entry.php` — applicant enters the emailed code in a separate step.
- [x] Build `proc-validate-cbt-code.php`:
  - [x] Look up the scheduled admission by the email stored in the verification session and `exam_code`.
  - [x] Reject if `NOW() > exam_expires_at` ("code expired").
  - [x] Reject if already taken (check `cbt_results` for an existing row on this `admission_id`).
  - [x] On success: start a session (`$_SESSION['applicant_admission_id']`), redirect to the assessment.

---

## Step 5: The assessment itself

Adapted from the mentor project's `test-question.php` + `end_test.php`.

- [x] Build `cbt-assessment.php` — pull questions from `cbt_questions` for the assigned `cbt_exams.id`.
- [x] Reuse the timer/countdown logic from `test-question.php`, scoped to `cbt_exams.duration_minutes` and the scheduled expiry.
- [x] Build `proc-submit-cbt.php`:
  - [x] Score against `cbt_questions.correct_option`.
  - [x] Compute `score` and `status` (`passed`/`failed`) based on `cbt_exams.pass_mark`.
  - [x] Insert into `cbt_results` with `admission_id`, `exam_id`, `score`, `status`.
- [x] On submit, update `admissions.status` to `cbt_completed` so the admin knows a final decision is needed.

---

## Step 6: Approval → member conversion

ok

- [x] In `proc-update-admission-status.php`, when status is set to `approved`:
  - [x] Insert a new row into `members` using the admission's name/email/phone.
  - [x] Generate a `member_code`.
  - [x] Assign the first available organization `zone_id` and `title_id` as defaults.
  - [x] Set a temporary hashed password so the member can log in via the Phase 5 member-login flow.
- [x] Send the approval email with next steps and login info at this point.

---

## Step 7: Org-scope everything new

- [x] Since this is built _after_ Phase 4, every new table, column, and query carries `org_id` from the start.
- [x] `cbt_exams`, `admissions`, and `cbt_results` use organization scope in admin processors and matching admission/exam joins in the applicant flow.

---

## Build order recommendation

1. **Step 1 + Step 2** together first — small, and everything else depends on them.
2. **Steps 3–5** as one connected unit — the mentor's reference files map directly onto them, so build/test the full schedule → code → assessment → score loop in one pass.
3. **Step 6** last — the member-conversion logic, once the pipeline above is proven working.
