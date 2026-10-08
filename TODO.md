# Associa8 Finish-Line Checklist

This is the implementation tracker for taking the current prototype to a secure, usable production MVP. Work top to bottom: decisions unblock schema and authorization work, which unblock complete user journeys and meaningful testing.

**Rule for checking items off:** mark a task complete only after the implementation exists and its acceptance condition has been verified. A page or database column existing is not, by itself, completion.

**Current baseline:** organization/admin signup, admin and member login routes, a relational schema, organization-scoped code in several admin modules, applicant CBT code/assessment flow, document upload/listing, and a number of member data views already exist. The current state and known code-level gaps are detailed in [PRODUCT-READINESS.md](PRODUCT-READINESS.md). This checklist does not assume those paths are production-safe until the tests below pass.

**Honest delivery status:** the admissions/CBT security work is substantially improved, but the app is still not complete enough to present as a finished product. The most visible unfinished areas are finance, attendance, and event workflows. These are still blockers for a boss-facing demo and must be treated as unfinished until they are tested and functional. Security hardening is important, but it does not replace basic feature completion.

## Project Status Snapshot (truth check)

This project is not in a "mostly finished" state. It is a mixed-status product with a secure core in some admission/CBT areas and multiple unfinished business modules still exposed in the UI.

### Module status by reality

#### Working or mostly working
- [x] Admin authentication and basic role/session checks
- [x] Organization scoping in key admission and CBT workflows
- [x] CBT access-code issuance, verification, expiry, and result prevention
- [x] Credential handling cleanup and safer local DB setup
- [x] Document access gating and safer file handling in key routes
- [x] Rerunnable schema/migration safety for CBT and rate-limit changes

#### Partially working / needs hard validation
- [ ] Admission management pages beyond the core flow
- [ ] Member profile and settings flows
- [ ] Document manager / upload experience end-to-end
- [ ] CBT admin pages and result review flow once a full real dataset is used
- [ ] Broader permission enforcement across remaining admin pages

#### Not ready for demo or production use
- [ ] Finance pages and any payment summary/dues logic that is still hardcoded or mock-driven
- [ ] Attendance pages and any real check-in/check-out workflow
- [ ] Event pages and RSVP booking flow
- [ ] Module assignment UI [admin/user-controls.php](admin/user-controls.php) must not present unfinished modules as working features

### Boss-facing rule
- A page or module is not complete because it exists in the admin sidebar or user-controls form.
- A module is only complete after it has a real DB-backed flow, role checks, validation, and a successful test path.
- Any unfinished module must either be removed from the visible UI, clearly marked as in-progress, or blocked from assignment until it is working.

### Immediate execution list
1. [ ] Audit all visible admin modules and remove or disable any feature that is still static/mock before showing the app to management.
2. [ ] Fix the Finance module completely: replace static cards and fake values with real tenant-scoped data and authorized workflows.
3. [ ] Fix the Attendance module completely: real attendance records, validation, duplicate prevention, and organization-scoped reporting.
4. [ ] Fix the Events/RSVP module completely: event ownership, capacity logic, booking/cancellation flow, and member access rules.
5. [ ] Re-check every permission toggle in [admin/user-controls.php](admin/user-controls.php) against the actual pages behind it.
6. [ ] Enforce module-level authorization consistently across all admin handlers and page loads, not just the CBT/admissions area.
7. [ ] Run a full end-to-end walkthrough for each major module using safe test data and record real pass/fail results.
8. [ ] After the working modules are proven, write the final boss-facing summary with only verified, working features listed.

## Phase 0: Agree On The Product

- [x] Write and approve the MVP user roles: platform operator, organization owner/admin, manager/staff with assigned permissions, member, and applicant.
- [x] Agree on the high-level access boundary: platform operator is cross-organization; organization admin is limited to its organization; staff are limited to assigned modules; members and applicants have limited self-service access.
- [x] Set permission defaults: org owner/admin has all access in their organization; managers get assigned modules but no staff/role management, organization settings, or refunds; staff get assigned task-level access, with sensitive actions restricted unless granted.
- [ ] Map those defaults to exact module/action permissions and implement enforcement in every relevant page and handler.
- [x] Decide whether `super_admin` means platform-wide access or organization-level superuser; it means platform-wide operator access. Organization owners/admins are scoped to their own organization.
- [x] Decide whether applicants self-apply publicly, are entered by staff, or both: both paths feed one admission workflow.
- [x] Decide whether members can be created manually, only by approved admissions, or by both paths: both direct creation and admission approval are supported.
- [x] Define a secure first-login and password-reset experience for admins and members: one-time email activation/reset links; users choose their passwords.
- [x] Decide whether payment collection is manual-only for MVP or requires an online provider: Paystack online collection for member dues plus audited staff-entered offline payments.
- [x] Select the MVP payment direction: Nigeria/NGN first; proceeds settle to each organization through a provider-supported linked merchant/subaccount arrangement; full refunds only; Associa8 subscription billing is deferred.
- [ ] Verify Paystack merchant eligibility, supported organization settlement model, fees, refund support, and webhook contract for the launch market before implementation.
- [x] Decide whether event creation/RSVP, staff-to-member messaging, and notification preferences are MVP requirements: yes, basic events; private asynchronous staff/member threads; in-app and email preferences. Defer waitlists, paid tickets, SMS, attachments, and broadcasts.
- [x] Decide MFA scope: defer MFA for launch; revisit after the core account security controls are in place.
- [ ] Select the production hosting/deployment target.
- [x] Set the compatibility baseline: maintained PHP 8.x and MySQL/MariaDB supported by the host; store timestamps in UTC, display Africa/Lagos time, use NGN for launch, and test current major desktop/mobile browsers.
- [ ] Define data retention, account deletion, privacy, consent, and export requirements for personal and financial records; owner has deferred this decision, so complete a policy/legal review before launch.
- [x] Approve the MVP acceptance statement: isolated Nigerian organization portals for admissions/CBT, members, documents, events/attendance, private messaging, notifications, and member dues paid through Paystack or recorded offline, with organization-scoped settlement and full refunds.

## Phase 1: Establish Safe Environments

- [ ] Remove database credentials from `inc/db.php` and load them from environment-specific configuration.
- [ ] Remove SMTP credentials from source and load them from a secret store/environment configuration.
- [ ] Rotate any database, SMTP, or other credential that has ever been committed, copied into a shared workspace, or used outside local development.
- [ ] Ensure local, test, staging, and production environments use separate databases, users, mail accounts, and secrets.
- [ ] Give each database environment a least-privilege database account; do not run the web app as a database administrator.
- [ ] Disable detailed PHP/MySQL errors in browser responses outside local development; retain safe server-side diagnostics.
- [ ] Configure HTTPS, secure cookies, production host/base URL, and trusted proxy behavior for the selected host.
- [ ] Decide how uploaded documents are stored in production and ensure the upload directory cannot execute PHP or other scripts.
- [ ] Remove personal/sample login credentials and test records from every production seed/import path.
- [x] Add credential-free database and mail configuration example templates.
- [x] Document local configuration setup and warn against committing credentials in `README.md`.
- [ ] Copy the templates into local ignored configuration and set Apache/PHP environment values; verify XAMPP uses them without falling back to credentials in PHP.
- [ ] Document test/staging/production configuration and credential rotation after the hosting target is selected.

## Phase 2: Make The Schema And Migrations Trustworthy

- [ ] Treat `associa8.sql` as a development fixture; create a clean production schema/migration path without sample customer data.
- [ ] Choose and install a repeatable migration tool or documented versioned SQL migration convention.
- [ ] Add a migration baseline matching the current schema and document the supported upgrade path.
- [ ] Rehearse a clean database install from scratch using only the documented instructions.
- [ ] Rehearse an upgrade from the current schema without losing member, admission, CBT, or document data.
- [ ] Create a data inventory for every table, identifying tenant-owned, member-owned, platform-owned, and derived records.
- [ ] Add or formally define tenant ownership for events and all event-related records.
- [ ] Decide how attendance, finance, messages, notifications, sessions, preferences, and activity logs inherit organization ownership.
- [ ] Add organization-safe ownership constraints or enforceable parent relationships for tenant-owned records.
- [ ] Change portal settings uniqueness from globally unique `portal_key` to the agreed organization/key identity.
- [ ] Decide whether member emails, member codes, application numbers, admin emails, and usernames are unique globally or per organization.
- [ ] Align database unique indexes and foreign keys with each approved uniqueness rule.
- [ ] Review every foreign key for delete/update behavior so deleting a parent cannot silently erase records that must be retained.
- [ ] Resolve the separate `users` versus `acc-info`/`admin-info` identity design before relying on staff permissions.
- [ ] Align `documents.uploaded_by` and `admissions.reviewed_by` with the selected staff identity model.
- [ ] Add indexes for organization-scoped listings and common member/event/transaction lookups.
- [ ] Add appropriate uniqueness constraints for one assessment attempt, one RSVP per member/event, and other business invariants.
- [ ] Make sample fixtures deterministic, clearly fake, and safe to load only into local/test environments.

## Phase 3: Enforce Tenant Isolation And Authorization

- [ ] Define one trusted way to obtain the active organization and actor identity from the authenticated session.
- [ ] Build a reusable admin authorization guard that requires authentication, active account status, valid organization context, and permitted role/module.
- [ ] Build a reusable member guard that confirms the member still exists, is active, and belongs to the session organization.
- [ ] Decide whether suspended members can log in to view status/history; implement the policy consistently.
- [ ] Apply the relevant guard to every admin and member page, including pages linked directly by URL.
- [ ] Apply authorization again inside every state-changing handler; never rely on a hidden button or sidebar visibility.
- [ ] Audit every SELECT, INSERT, UPDATE, DELETE, file download, export, and notification action for organization ownership.
- [x] Scope document reads, deletes, and downloads by organization; fix document deletion that currently selects/deletes by ID alone.
- [x] Scope suspension member lookup, status update, and history insert by organization; write the suspension `org_id`.
- [x] Validate that member creation zone, sub-zone, and title all belong to the active organization.
- [x] Validate that member edits cannot attach a foreign organization's title, zone, or sub-zone.
- [x] Scope member edit form data and update operations to the member's organization.
- [ ] Scope event lists and RSVP actions to the organization and event's organization.
- [ ] Scope portal setting reads and writes to the organization.
- [ ] Scope finance reads/writes to the member and organization using a trusted relationship.
- [ ] Scope messaging threads and replies to the signed-in member and organization.
- [ ] Scope admission, exam, question, result, status-change, and export operations to the organization.
- [ ] Scope staff/user creation and permission assignment to the organization, unless an explicitly platform-only actor performs it.
- [ ] Prevent super-admin-only access from being granted by missing/null session values or a default role.
- [ ] Review every SQL query containing a user-supplied ID for authorization, not just SQL injection safety.
- [ ] Add cross-organization negative tests for guessed IDs and forged POST bodies to every protected handler.

## Phase 4: Finish Authentication And Account Lifecycle

- [ ] Select one canonical staff identity model and migrate/retire the unused competing account flow.
- [ ] Ensure a staff login always resolves its linked admin profile and organization through explicit foreign keys.
- [ ] Reject login for disabled/unverified staff accounts and members under the agreed account policy.
- [x] Regenerate the session ID after successful admin authentication.
- [ ] Set session cookies `HttpOnly`, `Secure` in production, and an appropriate `SameSite` policy.
- [ ] Set session lifetime, idle timeout, logout invalidation, and concurrent-session policy.
- [ ] Add rate limiting and/or progressive delay for repeated failed login attempts.
- [ ] Add password reset with single-use, expiring, hashed reset tokens and email delivery.
- [ ] Add authenticated password change requiring the current password and appropriate CSRF protection.
- [ ] Remove the shared `Welcome123!` password and replace it with one-time invitation/reset links.
- [ ] Add member account invitation/activation for members created directly by staff.
- [ ] Make admission approval account activation recoverable if the approval email is delayed or rejected.
- [ ] Force a password change on first login when a temporary credential is used, or avoid sending temporary passwords entirely.
- [ ] Decide whether OTP in organization signup is real verification; implement it securely or remove the unused field and storage.
- [ ] Add email verification for organization/admin/member identities where required by the product.
- [ ] Implement MFA only if included in the agreed release; ensure UI state cannot claim it is enabled without server-side enrollment and verification.
- [ ] Add account disable/reactivation flows with appropriate audit history.
- [ ] Verify admin logout and member logout clear the correct session and redirect to the appropriate login page.

## Phase 5: Complete Organization Signup And Onboarding

- [ ] Validate all organization, administrator, username, and password fields on the server, including length and allowed values.
- [ ] Normalize email and username consistently before duplicate checks and inserts.
- [ ] Enforce the agreed uniqueness rules in the database to prevent concurrent duplicate signups.
- [ ] Make all signup validation errors clear and preserve safe non-secret form values after redirect.
- [ ] Keep organization, account, and admin-profile creation in one transaction and verify rollback behavior.
- [ ] Add a welcome/verification email and make failed delivery recoverable without creating duplicate organizations.
- [ ] Decide whether organization signups require platform approval and implement pending/active lifecycle if so.
- [ ] Create an onboarding step that configures organization profile, timezone/currency, first zone, first title, and portal settings.
- [ ] Scope and save portal settings per organization.
- [ ] Define what pricing plans mean in code, including member limits, plan changes, and trial expiration.
- [ ] Implement subscription checkout/billing only if it is in the agreed MVP; otherwise label plans as non-billing demo fields or remove them.
- [ ] Complete or remove the nonfunctional `admin/signup.php` shell to avoid a misleading alternate signup route.
- [ ] Test signup duplicate cases, transaction failure, email failure, and two concurrent signups.

## Phase 6: Complete The Admin Shell And Staff Controls

- [ ] Replace the hardcoded dashboard member, finance, attendance, application, chart, and activity figures with tenant-scoped queries.
- [ ] Add explicit empty, loading, query-error, and permission-denied states to dashboard widgets.
- [ ] Confirm each dashboard total uses a documented period, status definition, and timezone.
- [ ] Implement the agreed role and module permission matrix in server-side code.
- [ ] Restrict user creation, role changes, permission changes, disablement, and resets to authorized admins.
- [ ] Scope staff accounts and permissions to the organization; do not expose another organization's user records.
- [ ] Add edit, disable/reactivate, role update, module-permission update, and invite/reset actions for staff if staff accounts are in MVP.
- [ ] Log security-sensitive staff and permission changes with actor, organization, target, timestamp, and outcome.
- [ ] Replace placeholder profile names/roles in the shared admin header with authenticated session data.
- [ ] Fix logout navigation and every dead admin navigation route or remove the link.
- [ ] Make search, filter, pagination, export, and row-action controls work or remove them from the production interface.

## Phase 7: Finish Member, Title, Zone, And Suspension Workflows

- [ ] Make member code generation organization-aware if that is the agreed rule.
- [ ] Make member code generation safe under concurrent insertions using a transaction/sequence strategy and a unique constraint.
- [ ] Enforce member email uniqueness according to the approved organization/global policy.
- [x] Validate member-add fields and verify organization ownership of zone, sub-zone, and title before insert.
- [x] Complete member edit validation, ownership checks, duplicate handling, CSRF validation, and success/error feedback.
- [ ] Confirm member deletion behavior and retention policy; warn about cascading deletion of dependent records.
- [ ] Implement list search, filters, pagination, and export based on the actual member query.
- [ ] Remove mock member rows and use an honest empty state when a directory is empty.
- [ ] Wire title create/edit/delete/reorder actions and enforce organization-scoped level/name uniqueness.
- [ ] Wire zone and sub-zone create/edit/delete actions, validating the parent zone's organization on every handler.
- [x] Scope zone/title create/edit forms and handlers to the active organization, validate sub-zone parent ownership, and point title creation at its existing handler.
- [ ] Remove placeholder zone rows and `#` action links.
- [ ] Scope suspension/reinstatement lookup and mutation by organization and validate allowed status transitions.
- [ ] Persist `org_id` on suspension records and expose a correctly scoped history.
- [ ] Add concurrency-safe status updates and prevent duplicate active suspensions/reinstatements.
- [ ] Notify the member and authorized staff when suspension or reinstatement state changes, if required.
- [ ] Test organization boundaries, uniqueness, and status transitions for every membership handler.

## Phase 8: Complete Admissions From Intake To Member

- [ ] Decide and implement public applicant intake, staff-only intake, or both.
- [ ] If public intake is enabled, create the applicant form, validation, duplicate handling, consent/privacy notice, and rate limiting.
- [ ] Generate application numbers safely and enforce the selected organization-scoped uniqueness rule.
- [ ] Validate applicant and guarantor fields server-side and apply length limits/normalization.
- [ ] Scope applicant create/list/detail/update/export/status handlers by organization.
- [ ] Replace sample applicant fallback rows and placeholder application IDs with a real empty state.
- [ ] Add an applicant detail view containing the application, status history, CBT state/result, and authorized actions.
- [ ] Keep the status state machine explicit and reject invalid or repeated transitions in the database transaction.
- [ ] Record the acting admin and timestamp for every status transition.
- [ ] Ensure notification failures do not silently lose the underlying application update; provide retry visibility.
- [ ] On approval, create exactly one member and link the member to the admission record if that relationship is required.
- [ ] Scope approval duplicate-member checks to the correct organization and handle concurrent approvals.
- [ ] Assign default title/zone or require an onboarding step instead of creating a partly configured member silently.
- [ ] Send a secure account activation link rather than a reusable temporary password.
- [ ] Make rejection/approval email content configurable, escaped, and recoverable after SMTP failure.
- [ ] Decide whether applicants can see status or withdraw/update their application and implement only the agreed features.
- [ ] Test every transition from every status, duplicate approval, email failure, and cross-organization ID tampering.

## Phase 9: Make CBT Scheduling And Assessment Reliable

- [x] Persist the scheduled date/time in UTC, show it in Africa/Lagos time, and prevent code requests before the appointment; the 36-hour response window starts at the appointment.
- [ ] Test the CBT scheduled email end to end.
- [x] Use UTC for CBT appointment, response-window, attempt, and new result-submission timestamps; display staff-facing appointment/result times in Africa/Lagos time.
- [ ] Define whether global admission/CBT start/end settings gate applicant access; enforce those rules on the server.
- [ ] Ensure exam creation/update and question create/edit/delete handlers require organization ownership.
- [x] Prevent deleting questions from active exams or exams assigned to applicants/results; no exam edit/delete handler exists yet.
- [x] Create exams as drafts and allow activation only after the question set is complete; lock the set after activation.
- [x] Validate question text, answer options, correct answer, pass mark, duration, and minimum exam readiness.
- [x] Add a preview/review step showing all questions and correct answers before an exam becomes active.
- [x] Make exam assignment deterministic and persist the selected exam before issuing a code.
- [x] Generate cryptographically secure access codes, store them safely, and define code expiry/reuse behavior.
- [x] Rate-limit CBT access-code requests and verification attempts.
- [x] Avoid revealing whether an email belongs to a particular applicant more than the product permits in CBT code-request responses.
- [x] Make code issuance recoverable when SMTP fails after the code record is written; failed delivery requires requesting a replacement code.
- [x] Ensure one applicant cannot start multiple concurrent or repeated attempts unless explicitly allowed.
- [x] Enforce timer, eligibility, attempt count, and deadline on the server, not only in JavaScript/session UI.
- [x] Ensure answer submission accepts only question IDs and options belonging to the assigned exam.
- [x] Add a unique database constraint and transaction lock preventing duplicate results under concurrent submissions.
- [x] Confirm score rounding, skipped-answer behavior, pass-mark comparison, and result persistence rules.
- [x] Keep correct answers inaccessible in applicant-facing HTML/JSON before submission.
- [x] Replace mock CBT applicant/result rows and placeholder links with real records or empty states.
- [x] Add a read-only result detail view and document that scores/statuses are system-calculated with no manual override or separate approval state.
- [ ] Test valid/invalid codes, expiry, early/late access, no questions, malformed answers, duplicate submits, database rollback, and mail failure.

## Phase 10: Secure Document Lifecycle

- [ ] Enforce organization ownership on upload, list, delete, and download handlers.
- [ ] Ensure zone/sub-zone visibility is derived from an organization-owned hierarchy.
- [ ] Store uploads outside the public web root or serve them through an authorization-checked download endpoint.
- [ ] Validate actual MIME/content with a server-side file inspection tool, not only extension and user-selected type.
- [ ] Set an explicit maximum size and reject oversized uploads before moving them.
- [ ] Generate opaque server-side filenames and never trust a client-supplied path.
- [ ] Configure the storage directory to disallow script execution and directory listing.
- [ ] Handle upload database failure by removing the orphaned file; handle deletion failure without losing metadata unexpectedly.
- [ ] Check for path traversal and symlink edge cases in view/delete code.
- [ ] Align uploader attribution with the canonical staff account table.
- [ ] Add document replacement, retention, and audit policy if required.
- [ ] Test PDF/JPEG/PNG, malformed content, oversized files, unauthorized direct URLs, cross-tenant IDs, and storage failure.

## Phase 11: Implement Attendance End To End

- [ ] Define attendance record types: organization meeting, event attendance, member check-in, or other.
- [ ] Decide how attendance is captured: admin entry, QR/code check-in, import, or a combination.
- [ ] Replace the hardcoded `admin/attendance.php` table and stats with tenant-scoped records.
- [ ] Build an authorized attendance capture form/handler with duplicate prevention and audit metadata.
- [ ] Scope attendance reports through the member and organization relationship.
- [ ] Clarify whether `attendance_logs` and `event_attendance` overlap; use a clear source of truth.
- [ ] Validate check-in/check-out dates, status, event membership, and allowed corrections.
- [ ] Implement filter/search, date range, pagination, and export against real data.
- [ ] Make member attendance history show the event/meeting label and accurate status.
- [ ] Test duplicate check-ins, corrections, missing event links, timezone boundaries, and organization isolation.

## Phase 12: Implement Events And RSVPs

- [ ] Add organization ownership to events and migrate the schema/data safely.
- [ ] Build event list/create/edit/cancel/delete views and handlers with role and organization checks.
- [ ] Validate event date/time/timezone, capacity, visibility, and required fields.
- [ ] Add tenant-aware indexes and constraints for event/member RSVP uniqueness.
- [ ] Implement member RSVP and cancellation handlers with CSRF protection.
- [ ] Enforce event capacity atomically; define waitlist promotion and duplicate RSVP behavior.
- [ ] Ensure a member can only RSVP to their own organization's event.
- [ ] Add staff attendee list and attendance recording tied to the event.
- [ ] Add member-facing event details and accurate booked/waitlisted/cancelled states.
- [ ] Notify members of event changes, reminders, cancellations, and waitlist promotion if required.
- [ ] Test capacity races, duplicate submissions, cancellation, event edits, and cross-tenant access.

## Phase 13: Implement Finance And Payments

- [x] Confirm a Paystack test account is active and available for sandbox development.
- [ ] Define the exact dues/levy/charge model, amount and currency rules, fees, receipt requirements, and authorized recorders.
- [x] Decide MVP payment collection: Paystack for online member dues plus audited staff-recorded offline payments; Associa8 subscription billing is not in MVP.
- [ ] Add reliable organization ownership to transactions and payment obligations, directly or through constrained member ownership.
- [ ] Replace hardcoded financial cards/charts/table rows with tenant-scoped database queries.
- [ ] Build authorized admin workflows to create a charge/dues obligation and record an offline payment if that is MVP.
- [ ] Verify the supported Paystack linked-merchant/subaccount settlement model for Nigeria/NGN, then build checkout server-side and verify signed provider webhooks.
- [ ] Make provider callbacks idempotent and prevent duplicate transaction/receipt creation.
- [ ] Define pending/successful/failed/refunded transitions; do not accept status changes directly from the browser.
- [ ] Implement reconciliation and an exception view for payments with missing/duplicate callbacks.
- [ ] Generate receipts with unique references and verified amounts/currency.
- [ ] Show a member only their own transactions and organization dues.
- [ ] Implement admin-authorized full refunds only, verified with Paystack and recorded in immutable audit history; defer partial refunds.
- [ ] Add export with organization/role checks and safe CSV cell escaping.
- [ ] Test success, failure, timeout, replayed webhook, refund, amount mismatch, and tenant isolation.

## Phase 14: Finish Member Self-Service

- [ ] Wire the profile “Edit Information” control to the existing update handler and display validation/success feedback.
- [ ] Ensure profile reads/updates always target the authenticated member and organization.
- [ ] Add profile photo upload only with the same safe storage and validation rules as documents.
- [ ] Show current title, zone, membership dates, and status using accurate organization-scoped records.
- [ ] Add secure member password change and reset flows.
- [ ] Implement account/session management only if it can revoke real server-side sessions.
- [ ] Connect member settings controls to persisted preferences and validate every update.
- [ ] Use the member's stored date/time/currency settings consistently or remove controls that are not supported.
- [ ] Make member dashboard metrics match the underlying attendance, due, event, document, and notification definitions.
- [ ] Correct dashboard event/document counts to include only the member's organization and permitted visibility.
- [ ] Remove false static statuses such as hardcoded “Active” where the member record may differ.
- [ ] Add an accessible profile photo fallback and handle empty/missing member records safely.
- [ ] Test direct URL access after logout, suspended status, stale/deleted member sessions, and cross-member ID tampering.

## Phase 15: Complete Messaging And Notifications

- [ ] Define who can send a message, which members can receive it, and whether replies are supported.
- [ ] Build staff message compose/send UI and server-side handler scoped to the organization.
- [ ] Build member reply form and handler tied to a thread owned by that member.
- [ ] Support selecting a thread; do not always show only the first thread.
- [ ] Mark threads/messages read only after authorized viewing and display accurate unread counts.
- [ ] Add message validation, length limits, output escaping, CSRF protection, and rate limits.
- [ ] Decide whether messages are in-app only or also email/SMS; implement delivery and retry tracking accordingly.
- [ ] Enforce notification ownership through member/admission relationships and the organization.
- [ ] Add mark-read/unread and notification preference handlers.
- [ ] Persist email/SMS preferences and respect them in all background sends.
- [ ] Ensure organization broadcasts cannot include another organization's members.
- [ ] Test member/thread ID tampering, duplicate sends, unread state, and delivery failures.

## Phase 16: Repair Public Website And Conversion Paths

- [ ] Add the missing `send-mail.php` handler or change the contact form to a supported contact workflow.
- [ ] Validate contact input server-side, rate-limit spam, escape email output, and show recoverable success/failure states.
- [ ] Replace real-looking placeholder address, phone, and email details with verified organization contact information.
- [ ] Link primary signup/trial/demo calls to real destinations or remove them.
- [ ] Replace footer `#` links with valid pages, mail/phone links, or remove them.
- [ ] Correct the “Free Trail” wording and define what the trial/signup action promises.
- [ ] Confirm pricing page terms match actual plan enforcement and billing behavior.
- [ ] Verify public navigation, mobile navigation, branding links, and auth entry points.
- [ ] Add privacy policy, terms, and cookie/data disclosures appropriate to the deployed product.
- [ ] Check public forms for accessibility, mobile layout, validation, spam protection, and clear errors.

## Phase 17: Remove Prototype UI And Finish Usability

- [ ] Search all PHP/HTML for hardcoded demo names, metrics, dates, placeholder IDs, and fake transactions; remove or isolate them as fixtures.
- [ ] Search for `href="#"`, inert buttons, forms prevented from submitting, and links to missing routes; fix each or remove it.
- [ ] Ensure every visible create/edit/delete/export/filter/search action performs its advertised operation.
- [ ] Replace empty-list demo rows with an honest empty state and an authorized next action.
- [ ] Ensure confirmation dialogs, validation errors, and success messages work without exposing internal errors.
- [ ] Verify all layouts at narrow mobile, tablet, and desktop widths.
- [ ] Verify keyboard navigation, focus visibility, labels, accessible names, contrast, and screen-reader semantics.
- [ ] Check that tables have usable mobile behavior and stable pagination/filter state.
- [ ] Verify date/time, number, and currency formatting follows organization/member settings.
- [ ] Add consistent not-found, forbidden, expired-session, and service-unavailable responses.

## Phase 18: Automated Tests And Quality Gates

- [ ] Add an automated test runner and document the single command to run the full suite.
- [ ] Build isolated test database setup/teardown and deterministic seed fixtures for at least two organizations.
- [ ] Add unit tests for input validation, state transitions, score calculation, code generation, and formatting rules.
- [ ] Add integration tests for signup transaction/rollback and admin/member login/session lifecycle.
- [ ] Add tests for staff role and module permissions against direct GET and POST requests.
- [ ] Add cross-tenant read/write/delete/download tests for every organization-owned module.
- [ ] Add admission lifecycle and member-conversion tests, including duplicate and retry behavior.
- [ ] Add CBT tests for codes, time windows, expiry, answer validation, scoring, duplicate submits, and email failure.
- [ ] Add upload tests for valid/invalid content, size limits, direct URL access, and cleanup after database errors.
- [ ] Add events/attendance tests for RSVP capacity, duplicates, cancellation, and recording attendance.
- [ ] Add finance tests for idempotency, provider signature validation, reconciliation, and refunds if payment integration is in MVP.
- [ ] Add member tests for profile changes, settings, notifications, messaging, and suspended/deleted accounts.
- [ ] Run PHP syntax checks over every PHP file in CI.
- [ ] Add static analysis/linting appropriate to the chosen PHP version and resolve actionable findings.
- [ ] Add CI checks for tests, migrations, and secret scanning on every change.
- [ ] Require a green CI run and review of tenant/security-sensitive code before merging release work.

## Phase 19: Production Operations And Launch

- [ ] Document build/install/configuration steps and perform a production-like staging deployment.
- [ ] Store production secrets in the hosting platform's secret manager and verify access is restricted.
- [ ] Configure HTTPS redirects, secure session cookies, HSTS policy, and appropriate security headers.
- [ ] Configure error logs and alerts without returning sensitive diagnostics to users.
- [ ] Add health checks for PHP, database, mail delivery, and any payment provider.
- [ ] Add database backups with retention, encryption, and off-host storage.
- [ ] Perform and document a restore drill before launch.
- [ ] Define database migration/rollback procedure and test it on staging data.
- [ ] Add monitoring for login failures, mail failures, upload/storage issues, queue/backlog if used, and payment reconciliation.
- [ ] Set file upload quotas and monitor storage growth.
- [ ] Define incident response, credential rotation, privacy request, and security vulnerability procedures.
- [ ] Define support contact and a process for account recovery.
- [ ] Verify production mail authentication, sender identity, delivery, bounce behavior, and templates.
- [ ] Verify payment provider live/test mode separation and webhook secrets if applicable.
- [ ] Complete a privacy/security review of sample data, logs, email templates, and exports.
- [ ] Run the full acceptance checklist below on staging with two isolated organizations.
- [ ] Get product owner approval that MVP scope, known limitations, and launch criteria are satisfied.
- [ ] Deploy using the documented release and rollback procedure.
- [ ] Run post-deploy smoke tests for signup, both logins, one admin workflow, one member workflow, email, and tenant isolation.

## Finish-Line Acceptance Checklist

- [ ] Two organizations can independently sign up or be provisioned, authenticate their own admins, configure their own settings, and operate without seeing or changing each other's records.
- [ ] Admin access is role/module-based, enforced server-side on every route and handler, and auditable.
- [ ] A staff-created member can activate credentials, log in, view only their own organization's/member data, update their permitted profile fields, and log out.
- [ ] An applicant can complete the agreed application flow, progress through valid statuses, take one correctly timed CBT if required, and become one member on approval.
- [ ] Email-dependent flows recover safely from SMTP failure and can be retried without duplicate accounts, codes, members, or notifications.
- [ ] Authorized staff can manage members, titles, zones, documents, suspensions, events, attendance, admissions, and finance features that are in MVP.
- [ ] Members can complete each advertised profile, document, attendance, event, payment, message, notification, and settings action that is in MVP.
- [ ] Protected files and exports cannot be obtained by guessing IDs or using direct unauthenticated URLs.
- [ ] Sample content, dead links, fake dashboard numbers, and inert controls are absent from production-facing workflows.
- [ ] Fresh install, upgrade, automated test, backup, restore, deploy, and rollback procedures are documented and demonstrated.
- [ ] Staging acceptance and security checks pass; product owner explicitly approves launch.

## After MVP: Deliberate Follow-Up, Not Launch Blockers

- [ ] Evaluate SMS delivery and reminder scheduling.
- [ ] Evaluate richer analytics, report builder, and scheduled exports.
- [ ] Evaluate bulk member import with validation, preview, and rollback.
- [ ] Evaluate audit log search and organization data export.
- [ ] Evaluate localization and multiple currency support.
- [ ] Evaluate custom organization branding and domains.
- [ ] Evaluate mobile-first offline attendance capture if there is a validated need.
- [ ] Revisit feature requests using real customer feedback and operational data.
