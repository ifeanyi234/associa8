# Associa8 — Fix TODO (work top to bottom, each step is a standalone PR/commit)

## Phase 1 — Schema foundation (do this first, nothing else works without it) ✅

1. [ ] Add `org_id INT UNSIGNED NOT NULL` column to: `admin-info`, `acc-info`, `members`, `zones`, `titles`.
2. [ ] Add `org_id INT UNSIGNED NULL` column to: `documents`, `admissions`, `cbt_exams`, `cbt_results`, `suspensions`, `portal_settings` (nullable only where you decide org-wide defaults still make sense — otherwise NOT NULL).
3. [ ] Add FK constraints: `org_id` → `org-info(id)` on every table above.
4. [ ] Drop the global `UNIQUE` on `titles.level` and replace with `UNIQUE(org_id, level)`. Same for any other globally-unique column that should really be unique-per-org (e.g. `zones.name` → `UNIQUE(org_id, name)`).
5. [ ] Add `acc_id INT UNSIGNED` (FK → `acc-info.id`) column to `admin-info`, so login can join them properly instead of assuming matching IDs.
6. [ ] Backfill existing seed data with a single placeholder `org_id` so old rows don't break, or truncate test data and start fresh — your call.

## Phase 2 — Fix signup to actually link the three inserts ✅

7. [ ] In `proc-signup.php`, capture `mysqli_insert_id($conn)` right after the `org-info` insert.
8. [ ] Pass that org id into the `admin-info` insert (`org_id` column) and the `acc-info` insert is fine as-is, but capture its insert id too.
9. [ ] After the `admin-info` insert, set its new `acc_id` column to the `acc-info` insert id from step 8 (or insert `admin-info` first, `acc-info` second — either order works as long as you capture and link both IDs).
10. [ ] Move the duplicate-email/duplicate-username `SELECT` checks to _before_ any `INSERT` runs, not after.
11. [ ] Either wire up `admin/signup.php`'s form to a real backend, or delete it if `signup.php` (public) is the only intended entry point.

## Phase 3 — Fix login to use the real link ✅

12. [ ] In `proc-login.php`, after verifying `acc-info`, look up `admin-info` by `acc_id = acc-info.id` (not `admin-info.id = acc-info.id`).
13. [ ] Store `$_SESSION['org_id']` from that `admin-info` row — this becomes the scoping key for every page from here on.
14. [ ] Fix `admin/logout.php` to redirect to `admin/index.php`, not the public homepage.

## Phase 4 — Scope every existing admin query by org_id ✅

15. [ ] `admin/dashboard.php`, `member-directory.php`, `zones.php`, `titles-hierarchy.php`, `suspension.php`, `admission-management.php`, `cbt-*.php`, `financial.php`, `documents.php`, `attendance.php`, `user-controls.php` — add `WHERE org_id = $_SESSION['org_id']` (or `AND org_id = ...` alongside existing zone filters) to every query, **except** when `admin_role === 'super_admin'`, where org filtering is bypassed intentionally.
16. [ ] Same for every `proc-*.php` write handler — every `INSERT` into an org-scoped table must include `org_id` from session.
17. [ ] Update `admin/add-member.php`, `proc-add-member.php`, `add-zone.php`, `proc-add-zone.php`, `add-title.php`, `proc-add-title.php`, `add-admission.php`, `proc-add-admission.php`, `add-cbt-exam.php`, `proc-add-cbt-exam.php`, `add-cbt-question.php`, `proc-add-cbt-question.php`, `upload-document.php`, `proc-upload-document.php` the same way.

## Phase 5 — Build the actual member login flow ✅

18. [ ] Create `member-login.php` (public-facing, separate from `admin/index.php`) with a simple username/password (or email/password) form.
19. [ ] Create `proc-member-login.php`: verify against `members.email` + `members.password` (you'll need to backfill/hash passwords for existing member rows or force a "set password" flow for new members).
20. [ ] On success set `$_SESSION['member_id']`, `$_SESSION['member_org_id']`, `$_SESSION['member_zone_id']`, `$_SESSION['member_subzone_id']`.
21. [ ] Create `admin/member/inc/auth.php` (mirrors `admin/inc/auth.php` but checks `$_SESSION['member_id']`), and add `require_once` to the top of every file in `admin/member/`.
22. [ ] Decide: when admin creates a member (`proc-add-member.php`), does it auto-generate a temp password / send an invite email? Add that.

## Phase 6 — Wire the member dashboard pages to real data ✅

23. [ ] `admin/member/dashboard.php` — replace hardcoded "Joseph Raymond" etc. with a `SELECT` on `members WHERE id = $_SESSION['member_id']`.
24. [ ] `admin/member/profile.php` — same, pull real member row + editable form wired to a new `proc-update-member-profile.php`.
25. [ ] `admin/member/documents.php` — write the actual `$documents` query: `WHERE org_id = member_org_id AND ((zone_id IS NULL AND subzone_id IS NULL) OR zone_id = member_zone_id OR subzone_id = member_subzone_id)`.
26. [ ] `admin/member/attendance.php`, `payment.php`, `events.php`, `messages.php`, `settings.php` — same treatment: replace hardcoded blocks with real queries against `attendance_logs`, `finance_transactions`, `events`/`event_rsvps`, `message_threads`/`message_replies`, and member profile fields respectively (these tables already exist in `associa8.sql` and are currently unused — that's your data source).

## Phase 7 — Cleanup / secondary fixes ✅

27. [ ] Delete or implement `admin/user-controls.php`'s missing `process-user.php` backend.
28. [ ] Populate `admin/portal-settings.php` (it's currently empty) with a real form posting to the existing `proc-portal-settings.php`.
29. [ ] Move DB credentials out of `inc/db.php` into an environment variable / untracked config file.
30. [ ] Decide the fate of the unused `users` / `user_module_permissions` tables — either wire them into the FK-referencing code (`documents.uploaded_by`, `admissions.reviewed_by`) properly, or remove those FK references if `admin-info` is the real staff identity table going forward.

## Phase 8 — Verify isolation end to end

31. [ ] Sign up two separate organizations.
32. [ ] Log in as Org A's admin, create a zone, a member, upload a document.
33. [ ] Log in as Org B's admin — confirm none of Org A's zones/members/documents are visible.
34. [ ] Log in as the Org A member you created — confirm they only see Org A's org-wide + their own zone/subzone documents, and none of Org B's.
