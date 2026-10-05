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
3. Copy `inc/db.php.example` to the ignored local file `inc/db.php`, then set `ASSOCIA8_DB_HOST`, `ASSOCIA8_DB_NAME`, `ASSOCIA8_DB_USER`, and `ASSOCIA8_DB_PASSWORD` in the environment available to Apache/PHP. Restart Apache after changing Windows environment variables.
4. Install PHP dependencies from the project root with `composer install` if dependencies need to be restored.
5. Copy `inc/mail-config.php.example` to the ignored local file `inc/mail-config.php`, then set `ASSOCIA8_MAIL_HOST`, `ASSOCIA8_MAIL_USERNAME`, `ASSOCIA8_MAIL_PASSWORD`, and `ASSOCIA8_MAIL_PORT` in the Apache/PHP environment before trying email-dependent workflows. Optional sender values are `ASSOCIA8_MAIL_FROM`, `ASSOCIA8_MAIL_FROM_NAME`, and `ASSOCIA8_MAIL_REPLY_TO`.
6. Open the application through the local web server, for example `http://localhost/associa8/`.

The actual `inc/db.php` and `inc/mail-config.php` files are intentionally ignored local configuration; do not commit them or put credentials in the example templates. Rotate credentials if real values were ever committed. The SQL dump contains sample data and should not be imported as production customer data. There is no migration runner documented yet; schema changes must be planned and applied consistently to existing databases.

## Important Limitations

- Organization IDs exist in parts of the schema and many admin queries, but organization scoping is not complete across all tables and workflows.
- The admin session gate checks that a user is logged in; role and module permissions are not consistently enforced by the gate.
- Finance and admin attendance pages are largely mockups. Event, messaging, member settings, and several admin actions have incomplete write paths.
- The contact form points to a handler that is not present in the repository, and the separate `admin/signup.php` form is a nonfunctional shell.
- No automated test suite or CI workflow was found during the repository review. The application has not been verified end to end against a clean database in this documentation update.

## Contributor Notes

- Read [PRODUCT-READINESS.md](PRODUCT-READINESS.md) before extending a workflow; it records the current route chain and known gaps.
- Scope every read and write to the authenticated user's organization and, where applicable, member/zone ownership. Enforce this in the server-side handler, not only in the UI.
- Use prepared statements for database values, validate uploads by content as well as extension, escape rendered output, and add CSRF protection to state-changing forms.
- Update the schema and its setup/migration instructions with any data-model change.
- Do not commit real credentials, production data, or unredacted mail configuration.
