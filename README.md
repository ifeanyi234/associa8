# Associa8 — Project Guide (Actual Current State)

Associa8 is a PHP + MySQL (mysqli) membership management platform intended to let multiple **organizations** each run their own isolated portal: an admin side for staff and a member side for members. This document describes what the code **actually does today**, table by table and flow by flow — not the intended design. Gaps between intended and actual behavior are called out explicitly wherever they occur.

---

## 1. Folder structure

- `/` — public marketing site (`index.php`, `about.php`, `pricing.php`, `contact.php`, `signup.php`)
- `inc/` — public-site shared includes: `db.php` (mysqli connection), `navbar.php`, `mobile-nav.php`, `footer.php`, `cta.php`
- `admin/` — admin-facing app (super admin + scoped admin pages, all `proc-*.php` handlers)
- `admin/inc/` — admin-only includes: `auth.php` (session gate), `sidebar.php`, `preloader.php`, `footer.php`, `pagination.php`
- `admin/member/` — member-facing dashboard pages (**currently static mockups, not wired to real data or auth** — see §5)
- `admin/member/inc/` — member sidebar include
- `css/`, `js/`, `images/`, `videos/` — assets
- `associa8.sql` — full schema + seed/test data dump

---

## 2. Database schema — table by table, with what's missing

**None of the tables below have an `org_id` column.** This is the single biggest structural gap in the project (see §7).

| Table                                         | Purpose                                                                 | Key columns                                                                             | Gaps                                                                                                                                                                                             |
| --------------------------------------------- | ----------------------------------------------------------------------- | --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `org-info`                                    | One row per organization created at signup                              | `name`, `type`, `email`, `phone`, `country`, `state`, `pricing`, `total-members`        | No `id` is referenced anywhere else in the schema. Fully orphaned after insert.                                                                                                                  |
| `admin-info`                                  | Admin/staff profile (name, job title, role)                             | `id`, `role` (`super_admin`/`admin`/`manager`), `zone_id`, `subzone_id`                 | No FK to `org-info`. No FK to `acc-info` — login code assumes `admin-info.id == acc-info.id`, which is only true by coincidence (see `associa8.sql`: `acc-info` has 1 row, `admin-info` has 11). |
| `acc-info`                                    | Login credentials only                                                  | `id`, `username`, `password` (hashed), `otp`                                            | This is the **only** table checked at login. Not linked to `admin-info` by any real foreign key.                                                                                                 |
| `members`                                     | Organization members                                                    | `id`, `member_code`, `email`, `password`, `zone_id`, `subzone_id`, `title_id`, `status` | Has `email`/`password` columns clearly meant for member login, but **no login flow uses them yet**. No `org_id`.                                                                                 |
| `zones` / `subzones`                          | Geographic/organizational divisions used to scope members and documents | `id`, `name`, `coordinator_name`                                                        | No `org_id` — a "Lagos Zone" created by Org A is visible/usable by Org B's admin too.                                                                                                            |
| `titles`                                      | Membership hierarchy (Patron, President, Fellow, etc.) with `level`     | `id`, `title`, `level` (unique)                                                         | No `org_id`. `level` is globally unique, so two orgs can't both have a "level 1" title.                                                                                                          |
| `documents`                                   | Uploaded files, scoped by zone/subzone/org-wide                         | `zone_id`, `subzone_id` (both nullable = org-wide)                                      | Scoping is zone-based only, **not org-based** — see §6.                                                                                                                                          |
| `admissions`                                  | Applicant pipeline (pending → under_review → approved/rejected)         | `application_number`, `status`, guarantor fields                                        | No `org_id`. Every org's applicants share one pool.                                                                                                                                              |
| `cbt_exams` / `cbt_questions` / `cbt_results` | Computer-based testing module tied to admissions                        | —                                                                                       | No `org_id`.                                                                                                                                                                                     |
| `suspensions`                                 | Member suspension/reinstatement history                                 | `member_id`, `action_type`, `status`                                                    | Fine within a member, but member itself has no org boundary.                                                                                                                                     |
| `portal_settings`                             | Admission/CBT portal open/close windows                                 | `portal_key` (unique: `admission`, `cbt`)                                               | Global, not per-org — every org shares one admission window.                                                                                                                                     |
| `users` / `user_module_permissions`           | A **second, unused** user/permission system                             | —                                                                                       | Referenced by FK from `documents.uploaded_by` and `admissions.reviewed_by`, but nothing ever inserts into `users`. Currently dead code paths.                                                    |

---

## 3. The signup flow (`signup.php` → `proc-signup.php`)

A single 3-step form, submitted as one POST:

1. **Step 1 — Organization Information**: name, type, email, phone, country, state, pricing plan, member count.
2. **Step 2 — Administrator Info**: first/last name, email, phone, job title, role.
3. **Step 3 — Account Info**: username, password, confirm password, OTP.

`proc-signup.php` does, in order:

1. Validates password === confirm password.
2. Hashes the password (`password_hash`).
3. Runs three separate `INSERT` statements: one into `org-info`, one into `admin-info`, one into `acc-info`.
4. Wraps them in `mysqli_begin_transaction` / `mysqli_commit`, so all three succeed or all three roll back together.
5. **Does not capture or store any of the returned insert IDs against each other.** After commit, `org-info.id`, `admin-info.id`, and `acc-info.id` are three independent auto-increment values with no recorded relationship.
6. On success, redirects to `signup.php?status=success`.

**Uniqueness checks are broken as written**: `proc-signup.php` runs `SELECT` queries against `org-info`/`admin-info`/`acc-info` for existing email/username, but only _uses_ the results in the `else if` branches after the inserts already ran — meaning duplicate-detection logic can't actually block a duplicate insert from happening first. Also note there's a separate, simpler `admin/signup.php` (an admin-only registration form) whose submit handler is `onsubmit="event.preventDefault()"` — it does nothing; it's a UI shell with no backend at all.

---

## 4. The login flow (`admin/index.php` → `admin/proc-login.php`)

1. Form posts `username`/`password` to `proc-login.php`.
2. Looks up the row in `acc-info` by username.
3. `password_verify()`s the password against the hash.
4. On success: sets `$_SESSION['user_id']` and `$_SESSION['username']` from the `acc-info` row.
5. **Then** runs `SELECT role, zone_id, subzone_id FROM admin-info WHERE id = $user_id`, using the `acc-info` ID as if it were the `admin-info` ID.
   - If a matching row happens to exist, session gets `admin_role`, `admin_zone_id`, `admin_subzone_id` from it.
   - If no matching row exists (the realistic case, since these are independent counters), it silently falls back to `admin_role = 'admin'`, `zone_id = null`, `subzone_id = null` — meaning **every login that doesn't luckily hit a matching row becomes an unscoped generic admin.**
6. Redirects to `admin/dashboard.php`.

**There is exactly one login destination: the admin dashboard.** There is no role check at login that could route a "member" account anywhere else, because member accounts don't log in through this flow at all (see §5).

`admin/inc/auth.php` is the gate every protected `admin/*.php` page includes: it just checks `$_SESSION['user_id']` is set, redirecting to `admin/index.php` if not. It does **not** re-verify role or org scope on every page — each page trusts whatever `admin_role`/`admin_zone_id` landed in session at login time.

`admin/logout.php` clears `$_SESSION`, destroys the session, and redirects to `index.php` (the _public_ homepage, not `admin/index.php` — worth checking if that's intentional).

---

## 5. The member side — currently a disconnected mockup

`admin/member/dashboard.php`, `profile.php`, `attendance.php`, `payment.php`, `events.php`, `messages.php`, `documents.php`, `settings.php`:

- **None of them `require_once "inc/auth.php"`** (or any auth check). They are reachable directly by URL by anyone.
- All content is **hardcoded HTML** — "Joseph Raymond", "90%", "₦10,000 due" — not pulled from the `members` table.
- `admin/member/documents.php` is the one partial exception: it references a `$documents` variable (filtered by zone/subzone) but the query that would populate `$documents` isn't present in that file — it's presumably meant to mirror `admin/documents.php`'s scoping logic but doesn't yet.
- There is no `member-login.php`, no `proc-member-login.php`, and no session variables (`member_id`, `member_zone_id`, etc.) ever get set anywhere in the codebase.
- The `members` table already has `email` and `password` columns sitting ready for this — they're just never read at login time by any script.

**Conclusion: today, a member never logs in.** The member dashboard is a design/frontend deliverable only.

---

## 6. Document visibility — how scoping works today (and its real boundary)

`admin/documents.php` (admin's document manager) reads the logged-in admin's session (`admin_role`, `admin_zone_id`, `admin_subzone_id`) and:

- If `admin_role === 'super_admin'` OR `admin_zone_id` is null → shows **all** documents, no filtering.
- Otherwise → shows documents where `(zone_id IS NULL AND subzone_id IS NULL)` (org-wide) `OR zone_id = admin's zone` `OR subzone_id = admin's subzone`.

`admin/upload-document.php` → `admin/proc-upload-document.php` lets the uploader pick Organization-wide / Specific zone / Specific sub-zone, validates the chosen sub-zone actually belongs to the chosen zone, and inserts accordingly.

**This is zone/subzone scoping, not organization scoping.** Because `zones`/`subzones`/`documents` have no `org_id`, this logic only correctly separates "people in Zone A" from "people in Zone B" _within what the schema currently treats as one single shared organization_. Two different signed-up organizations, today, share the exact same pool of zones and documents. An admin from Org B logging in — if their `admin-info` row happens to have no zone assigned, or matches an Org A zone by ID — can see Org A's documents.

---

## 7. The core structural gap: no organization boundary anywhere

This is the issue your project needs solved before "multiple organizations, isolated data" is true:

- No table has `org_id`.
- `admin-info` and `acc-info` aren't linked by a real foreign key — only by a coincidental matching auto-increment ID.
- `zones`, `subzones`, `titles`, `members`, `documents`, `admissions`, `cbt_exams`, `cbt_results`, `suspensions`, `portal_settings` are all **global tables shared by every signed-up organization.**
- Practical consequence: right now, every organization that signs up is, from the database's point of view, contributing to and reading from **one shared organization's worth of data.**

---

## 8. The actual current loop (as code, not as intended)

```
signup.php → proc-signup.php
   ├─ INSERT org-info        (orphaned — id never reused)
   ├─ INSERT admin-info      (orphaned — id never reused)
   └─ INSERT acc-info        (only this is used to log in)
              │
              ▼
admin/proc-login.php
   ├─ verify against acc-info
   ├─ SELECT admin-info WHERE id = acc-info.id   ← coincidental match, often fails silently
   └─ session: user_id, admin_role, admin_zone_id, admin_subzone_id
              │
              ▼
admin/dashboard.php  (the ONE login destination — role read from session, no member routing exists)
              │
              ▼
Admin creates members via admin/add-member.php → proc-add-member.php
   → INSERT members (zone_id, subzone_id, title_id — no org_id)
              │
              ▼
admin/member/*.php  ← reachable by anyone, unauthenticated, shows hardcoded mock data
              │
              X   (dead end — no member session, no member login exists, loop does not close)
```

**The loop does not currently close into members.** It stops at "admin creates a member row in the database." Nothing lets that member authenticate and view their own scoped dashboard.

---

## 9. Known secondary issues worth fixing alongside the above

- `admin/signup.php` — non-functional UI shell (`onsubmit="event.preventDefault()"`), no backend.
- `proc-signup.php` duplicate-checks run _after_ the inserts they're meant to guard against.
- `users` / `user_module_permissions` tables exist and are referenced by FKs (`documents.uploaded_by`, `admissions.reviewed_by`) but are never inserted into — `admin/user-controls.php`'s form posts to `process-user.php`, which doesn't exist in the provided codebase.
- `admin/logout.php` redirects to the public homepage rather than the admin login page.
- `admin/portal-settings.php` is an empty file (0 bytes) despite `proc-portal-settings.php` existing and expecting to render a form into it.
- `inc/db.php` has hardcoded DB credentials committed to source control.

---

## 10. What "done" looks like

1. Every org-scoped table carries `org_id`, and every query that touches those tables filters by the logged-in user's `org_id`.
2. `admin-info` and `acc-info` are linked by a real foreign key, not a coincidental shared ID.
3. A real member login flow exists, authenticating against `members.email`/`members.password`, setting a member session, and the `admin/member/*.php` pages query real data scoped to that member's org + zone/subzone instead of showing hardcoded mockup content.
4. `admin/inc/auth.php`-equivalent guard added to every `admin/member/*.php` page.
5. Super admin (`role = 'super_admin'`) is the only role allowed to bypass org/zone scoping, and that check happens consistently everywhere scoping is enforced.
