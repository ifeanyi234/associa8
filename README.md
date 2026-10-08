# Associa8

Associa8 is a PHP and MySQLi membership-management portal for organizations. Its intended product combines organization administration, member self-service, admissions, and computer-based assessments in one application.

The repository is an active prototype, not a production-ready release. Several end-to-end flows are implemented, but other visible modules remain static or have controls without handlers. Multi-organization data isolation and authorization still need a full pass. See [PRODUCT-READINESS.md](PRODUCT-READINESS.md) for the current workflow map and [TODO.md](TODO.md) for the sequenced finish-line checklist.

## Current Capabilities

- Public marketing pages, organization signup, and admin/member login screens.
- Admin workflows for member, title, zone, document, admission, CBT, suspension, and portal-setting records, with varying levels of completion.
- Applicant CBT flow: email a code, verify it, take an assessment, score it, and display a result.
- Member pages for profile, documents, attendance history, events, messages, and transactions; some read from the database, while their related write actions are incomplete.
- PHPMailer integration for assessment and admission emails.

Treat dashboard figures, seed records, and controls without a POST handler as presentation/demo content until their underlying workflow is verified.

## Stack And Layout

- PHP 8.x with MySQLi, MySQL or MariaDB, and Apache (XAMPP is suitable locally).
- `associa8.sql` contains the schema and sample/test data.
- `inc/` contains the shared database connection, mailer, and public-site includes.
- `admin/` contains the admin portal, request handlers, and `admin/member/` member portal.
- `css/`, `js/`, `images/`, and `videos/` contain frontend assets.
- `vendor/` contains Composer-managed PHPMailer dependencies.

## Local Setup

1. Put the project in the web server document root.
2. Create a local database named `associa8` and import `associa8.sql`.
3. Set `ASSOCIA8_DB_HOST`, `ASSOCIA8_DB_NAME`, `ASSOCIA8_DB_USER`, and `ASSOCIA8_DB_PASSWORD` in the environment available to Apache/PHP. For local development only, if none of these variables are set, `inc/db.php` can use the ignored `inc/db.local.php` file; set its values to the credentials for your local database. Restart Apache after changing Windows environment variables.
4. Install PHP dependencies from the project root with `composer install` if dependencies need to be restored.
5. Copy `inc/mail-config.php.example` to the ignored local file `inc/mail-config.php`, then set `ASSOCIA8_MAIL_HOST`, `ASSOCIA8_MAIL_USERNAME`, `ASSOCIA8_MAIL_PASSWORD`, and `ASSOCIA8_MAIL_PORT` in the Apache/PHP environment before trying email-dependent workflows. Optional sender values are `ASSOCIA8_MAIL_FROM`, `ASSOCIA8_MAIL_FROM_NAME`, and `ASSOCIA8_MAIL_REPLY_TO`.
6. Open the application through the local web server, for example `http://localhost/associa8/`.

The actual `inc/db.php`, `inc/db.local.php`, and `inc/mail-config.php` files are intentionally ignored local configuration; do not commit them or put credentials in the example templates. On a live server, configure environment variables in the hosting environment or secret manager rather than reusing local development credentials. Rotate credentials if real values were ever committed. The SQL dump contains sample data and should not be imported as production customer data. There is no migration runner documented yet; schema changes must be planned and applied consistently to existing databases.

### Local finance and attendance demo data

To populate the local `associa8` database with clearly labeled sample records, run `php tests/seed_demo_finance_attendance.php` from the project root. The script refuses non-CLI execution and non-local databases. It adds synthetic `DEMO-FIN-*` members, finance transactions, and general attendance records under organization ID 1; its SQL is safe to rerun and does not overwrite existing rows. Remove only those demo records with `php tests/cleanup_demo_finance_attendance.php`. Do not load these fixtures into a shared or production database.

### Applying a database migration

For a **new local or production database**, import `associa8.sql` only; it already contains the current schema. Do not run the upgrade scripts after importing that dump.

For an **existing database that already contains Associa8 data**, back it up and run the scripts in `migrations/` in date/name order before deploying code that depends on them. The scripts are designed to be safe to re-run: existing columns/tables/indexes are skipped, and the unique-results script reports duplicates instead of trying an index operation that would fail. The CBT-code migration revokes any old plaintext codes; affected applicants must request a fresh code. These scripts are compatible with the supported MySQL/MariaDB setup, including the local MariaDB 10.4 version.

CBT rate limits currently allow 20 access-code requests per IP per hour, 20 code verifications per IP per 15 minutes, and 5 code verifications per applicant per 15 minutes. Counters retain hashed subjects and are eligible for cleanup after one day.

CBT scores give every question equal weight. Unanswered questions count as incorrect; the score is the percentage correct rounded to the nearest whole number, and an applicant passes when that rounded score is greater than or equal to the exam pass mark. Results are saved together with the admission's `cbt_completed` state in one database transaction. Correct-answer fields are loaded only by the submission handler and are not sent to the applicant's assessment page.

CBT result review is read-only: the recorded score and pass/fail status are calculated automatically from the exam answers and pass mark. Staff can view the result details, but there is no manual score override or separate result approval state. The admission workflow continues separately after CBT completion.

CBT exams created in the admin area start as drafts. Staff can add or remove questions while an exam is a draft and unused; a review page shows the full question set and correct answers before activation. Activation requires at least one complete question and locks the question set. Scheduling and access-code requests also reject active exams with missing or incomplete questions. There are currently no exam edit or delete actions.

CBT appointment times are entered in Africa/Lagos time and stored in UTC. Response deadlines, attempt start/expiry checks, and new result submission timestamps use UTC in the database; staff see appointment and result-submission times in Africa/Lagos time. Applicants cannot request an access code before the saved appointment time. The 36-hour response window starts at that time; the exam timer itself starts when the applicant enters the code. Existing databases need the `20261007_persist_cbt_schedule_time.sql` migration. Older results retain their original database-generated `taken_at` values and may reflect the database server timezone used at the time.

## Important Limitations

- Organization IDs exist in parts of the schema and many admin queries, but organization scoping is not complete across all tables and workflows.
- Admin login verifies a separate password hash per account; protected admin pages and handlers enforce the account's active state, organization, and preset role.
- Organization User Control sends a 48-hour invitation to grant someone access to the organization's existing workspace. The invitee sets their own password; the invitation token is stored hashed and the organization role applies when they sign in. Organization admins can manage their own staff; platform super admins can select an organization. Existing databases need `migrations/20261008_staff_user_control.sql` and `migrations/20261008_staff_invitations.sql`. Invitation mail requires the configured SMTP settings. Password reset email and staff-account audit history are not yet implemented.
- The finance page reads organization-scoped transaction records and supports administrator-recorded offline payments with an activity-log entry. Dues creation, Paystack checkout/webhooks, refunds, receipts, export, and reconciliation are not implemented end to end.
- Admin attendance uses a list page and a separate add-attendance page, with organization-scoped manual records and filtering. Attendance is not yet linked to events, and pagination/export/correction workflows remain unfinished.
- Events now have organization-scoped admin creation/cancellation and member bookings with capacity limits, waitlisting, cancellation, and waitlist promotion. Existing databases need `migrations/20261008_events_tenant_scope.sql`; it leaves pre-existing events unassigned intentionally, so map each legacy event to its correct organization before making it visible. Also apply `migrations/20261008_fix_unlimited_event_capacity.sql` to normalize legacy zero-capacity events to unlimited and promote members who were incorrectly waitlisted. Event editing, staff event-attendance capture, notifications, and browser end-to-end testing remain incomplete.
- Messaging, member settings, and several admin actions have incomplete write paths.
- The contact form points to a handler that is not present in the repository, and the separate `admin/signup.php` form is a nonfunctional shell.
- No automated test suite or CI workflow was found during the repository review. The application has not been verified end to end against a clean database in this documentation update.

## Contributor Notes

- Read [PRODUCT-READINESS.md](PRODUCT-READINESS.md) before extending a workflow; it records the current route chain and known gaps.
- Scope every read and write to the authenticated user's organization and, where applicable, member/zone ownership. Enforce this in the server-side handler, not only in the UI.
- Use prepared statements for database values, validate uploads by content as well as extension, escape rendered output, and add CSRF protection to state-changing forms.
- Update the schema and its setup/migration instructions with any data-model change.
- Do not commit real credentials, production data, or unredacted mail configuration.
