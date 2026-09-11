# Associa8 External AI Handoff Prompt

Copy the prompt below into ChatGPT, Claude, Gemini, or another coding AI. Use it as a project handoff document, then give the AI only the files needed for the current task.

---

## Prompt

You are helping me continue an existing PHP and MySQL project called **Associa8**.

This is not a small demo. It is a partially built association-management platform, and I am working on it in small pieces because I may be using a free AI plan with message and context limits.

Your job is to help me finish the project gradually without rewriting everything, breaking existing work, or pretending that placeholder data is already connected to the database.

## How you must work

1. Read the files I provide before suggesting changes.
2. Do not assume that a page is database-connected just because it has HTML that looks like a dashboard.
3. Separate these three states clearly:
   - hard-coded placeholder UI
   - PHP code that reads from MySQL
   - PHP code that writes to MySQL
4. Work on one feature slice at a time.
5. Keep changes small enough to fit within a free-plan response.
6. Do not redesign unrelated pages.
7. Do not delete existing user work without explaining why.
8. Do not create duplicate logic when a shared include or helper already exists.
9. Preserve existing CSS classes and visual patterns unless a change is necessary.
10. Use prepared statements for new database writes and avoid putting raw user input directly into SQL.
11. Escape database values before displaying them in HTML.
12. Validate all submitted fields on the server, not only in JavaScript.
13. Tell me exactly which files you changed and why.
14. After editing, give me the exact validation commands to run.
15. If the task is too large for one response, stop at a clean checkpoint and tell me the next small task.

## Important response format

Before editing, respond with:

- What you understand
- The exact files you need from me
- One small implementation slice you recommend
- One risk or dependency

After editing, respond with:

- What changed
- How the data flows now
- What I must run in phpMyAdmin or the terminal
- How to test it manually
- What remains unfinished

Do not claim that a feature is complete if only the UI has been created.

## Project structure

The project is located at:

```text
associa8/
├── about.php
├── contact.php
├── index.php
├── pricing.php
├── proc-signup.php
├── send-mail.php
├── signup.php
├── associa8.sql
├── admin/
│   ├── index.php
│   ├── proc-login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── admission-management.php
│   ├── attendance.php
│   ├── cbt-applicants.php
│   ├── cbt-questions.php
│   ├── cbt-results.php
│   ├── documents.php
│   ├── financial.php
│   ├── member-directory.php
│   ├── portal-settings.php
│   ├── suspension.php
│   ├── titles-hierarchy.php
│   ├── upload-document.php
│   ├── user-controls.php
│   ├── zones.php
│   ├── add-title.php
│   ├── add-zone.php
│   ├── proc-add-title.php
│   ├── proc-add-zone.php
│   ├── inc/
│   │   ├── footer.php
│   │   ├── preloader.php
│   │   └── sidebar.php
│   └── member/
│       ├── dashboard.php
│       ├── attendance.php
│       ├── documents.php
│       ├── events.php
│       ├── messages.php
│       ├── payment.php
│       ├── profile.php
│       ├── settings.php
│       └── inc/sidebar.php
├── inc/
│   ├── db.php
│   ├── navbar.php
│   ├── mobile-nav.php
│   ├── footer.php
│   └── cta.php
├── css/
│   ├── styles.css
│   ├── dashboard.css
│   └── preloader.css
├── js/
│   ├── modal.js
│   ├── dashboard-tools.js
│   ├── video-modal.js
│   ├── mobilemenu.js
│   ├── fixedtop.js
│   └── preloader.js
└── videos/
```

The structure may change. Treat the actual files I provide as authoritative.

## Current technical stack

- PHP 8
- MySQL/MariaDB through MySQLi
- XAMPP on Windows
- Server-side rendered PHP pages
- Plain JavaScript
- CSS without a frontend framework
- phpMyAdmin for database management

The database connection is in `inc/db.php`.

## Current database areas

The SQL dump currently includes these main tables:

- `acc-info` for login usernames, password hashes, and OTP values
- `admin-info` for organization administrator details
- `org-info` for organization details
- `users` for a newer user-management design
- `members` for member profiles
- `titles` for membership hierarchy titles
- `zones` for parent geographic zones
- `subzones` for child zones linked to `zones.id`
- `suspensions` for member suspension and reinstatement records
- `documents` for uploaded documents
- `events` and event attendance/RSVP tables
- finance tables
- attendance tables
- message and notification tables
- permissions and activity-log tables

Important relationships include:

```text
zones.id       -> members.zone_id
zones.id       -> subzones.zone_id
members.id     -> suspensions.member_id
members.id     -> attendance and message records
members.title_id -> titles.id
```

Use the exact current SQL dump I provide because schema changes may have been made manually in phpMyAdmin after the dump was generated.

## Current authentication situation

The admin login form is in `admin/index.php`.

The login processor is `admin/proc-login.php`.

The login currently looks up a username in `acc-info` and verifies the stored password hash with `password_verify()`.

Successful login stores session values such as:

```php
$_SESSION['user_id']
$_SESSION['username']
```

Logout is handled by `admin/logout.php`.

Important unfinished authentication work may include:

- adding session guards to every protected admin page
- separating admin accounts from member accounts
- checking user roles and permissions
- adding CSRF protection
- adding login rate limiting
- deciding whether `acc-info` or `users` should become the final authentication table

Do not silently merge the old and new authentication designs. Point out the conflict first.

## Current feature state

Several dashboard pages began as visual prototypes. Some tables contain hard-coded placeholder rows.

The title and zone creation flow has been started:

- `admin/add-title.php` contains the title form
- `admin/proc-add-title.php` saves titles
- `admin/add-zone.php` contains zone and sub-zone forms
- `admin/proc-add-zone.php` saves zones and sub-zones
- `admin/titles-hierarchy.php` has begun reading saved titles
- `admin/zones.php` has summary counts
- `admin/member-directory.php` has some live counts but may still contain placeholder rows

Do not treat these pages as fully complete. For every page, identify whether it is:

- display-only placeholder
- database read-only
- database create-capable
- database update-capable
- database delete-capable

## Recommended development order

Work through the project in vertical slices rather than trying to finish every page at once.

### Slice 1: Database and shared foundations

- Confirm the current schema
- Resolve duplicate or conflicting table designs
- Confirm primary keys, unique keys, indexes, and foreign keys
- Confirm the database connection
- Create small reusable authentication and authorization includes if appropriate

### Slice 2: Titles and zones setup

- Display real titles from `titles`
- Display real zones from `zones`
- Display real sub-zones from `subzones`
- Add title and zone forms
- Add server-side validation
- Handle duplicate errors clearly through the existing modal

### Slice 3: Member creation

- Build an Add Member page
- Load titles and zones into dropdowns
- Save a member into `members`
- Generate or validate a unique member code
- Show the new member in the directory

### Slice 4: Member directory

- Replace placeholder rows with database results
- Add search and status filtering
- Add pagination
- Add view, edit, and delete behavior
- Display the related title and zone using joins

### Slice 5: Suspension and reinstatement

- Display real records from `suspensions`
- Add a suspension form tied to a member
- Update member status consistently
- Add reinstatement behavior
- Keep an audit trail where appropriate

### Slice 6: Other modules

Only after the previous slices work should you continue with:

- attendance
- payments and finance
- events
- documents
- messages
- notifications
- admissions
- CBT
- portal settings
- permissions

### Current CBT progress

The CBT area now has these database-backed pieces:

- `cbt_exams` stores exam title, duration, pass mark, and status.
- `cbt_questions` stores multiple-choice questions linked to `cbt_exams`.
- `cbt_results` stores scores linked to `cbt_exams` and `members`.
- `admin/add-cbt-exam.php` and `admin/proc-add-cbt-exam.php` create exams.
- `admin/add-cbt-question.php` and `admin/proc-add-cbt-question.php` create questions.
- `admin/proc-delete-cbt-question.php` deletes questions with a POST request.
- `admin/cbt-questions.php` displays saved questions and their answers.
- `admin/cbt-applicants.php` reads applicants from `admissions`.
- `admin/cbt-results.php` reads results from `cbt_results`, `members`, and `cbt_exams`.

The CBT question table migration is located at:

```text
admin/inc/cbt-questions-migration.sql
```

The next CBT work should be done one slice at a time:

1. Add/edit/delete exams safely.
2. Add edit-question support.
3. Create an applicant-to-exam assignment table or clearly define how `admissions` become CBT participants.
4. Add exam codes and expiry dates if the applicant design requires them.
5. Build the member-facing exam attempt flow.
6. Insert and display real `cbt_results` after an attempt.

Do not invent CBT scores or exam codes in the dashboard. Placeholder rows should appear only when the relevant database table is empty.

## Database safety rules

Before changing a table:

1. Check whether the table already exists.
2. Check whether the column already exists.
3. Check existing data for duplicates or invalid foreign-key values.
4. Use a migration or a clearly marked SQL block.
5. Do not tell me to drop a table unless you clearly warn that its data will be destroyed.
6. Prefer additive changes for a project that already contains data.

Useful inspection queries include:

```sql
SHOW CREATE TABLE `members`;
SHOW CREATE TABLE `titles`;
SHOW CREATE TABLE `zones`;
SHOW CREATE TABLE `subzones`;
SHOW CREATE TABLE `suspensions`;

SELECT COUNT(*) FROM `members`;
SELECT COUNT(*) FROM `titles`;
SELECT COUNT(*) FROM `zones`;
SELECT COUNT(*) FROM `subzones`;
SELECT COUNT(*) FROM `suspensions`;
```

## Testing rules

For every change, test the smallest realistic behavior:

- PHP syntax check with `php -l file.php`
- JavaScript syntax check with `node --check file.js`
- Open the page in the browser
- Submit a valid form
- Submit an invalid form
- Try a duplicate value when a unique constraint exists
- Confirm the database row was actually created or changed
- Confirm the result appears on the relevant dashboard
- Confirm refresh does not accidentally repeat a submission

Do not use only screenshots or only static code inspection as proof that a database feature works.

## How to send work to you in small chunks

Because I may be using a free AI plan, I will usually send one of these small bundles:

### For a page read feature

```text
1. The page PHP file
2. inc/db.php
3. The relevant SQL table definition
4. Any shared include used by the page
```

### For a form submission feature

```text
1. The form page
2. The processor PHP file, if one exists
3. inc/db.php
4. The relevant CREATE TABLE statement
5. The page that should display the saved record
```

### For a bug

```text
1. The exact error message
2. The current file producing the error
3. The file that calls or includes it
4. The relevant database definition, if the error involves MySQL
5. What I expected to happen
6. What actually happened
```

Do not ask me to paste the entire project unless it is genuinely necessary. Request only the smallest files needed for the current slice.

## First task for the external AI

Start by asking me for the smallest set of files needed to complete exactly one of these tasks:

- make the membership directory load real members
- make titles and zones list real saved records
- add members with title and zone selection
- make suspension records work
- protect all admin pages with sessions

Choose one task only. Explain why it should come before the others, then implement it carefully.

Remember: the goal is steady, verifiable progress through a large existing project, not a giant rewrite in one answer.

---

## Suggested first message after pasting the prompt

```text
I want to start with one vertical slice only: make the membership directory load real members from MySQL.

Please ask me for the minimum files you need, inspect them first, and then tell me the smallest safe change. Do not touch titles, zones, suspension, authentication, or unrelated dashboard pages yet.
```
