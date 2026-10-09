<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/inc/info-tip.php';

$plainToken = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$validTokenShape = preg_match('/^[A-Za-z0-9_-]{40,50}$/', $plainToken) === 1;
$error = '';
$success = false;
$invitation = null;

if (!$validTokenShape) {
    $error = 'This invitation link is invalid or incomplete.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $sessionCsrf = $_SESSION['staff_invitation_csrf'] ?? '';
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
    if (!is_string($csrf) || !is_string($sessionCsrf) || $sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
        $error = 'This form expired. Reopen your invitation link and try again.';
    } elseif (strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Choose a password between 12 and 72 characters.';
    } elseif (!hash_equals($password, $passwordConfirmation)) {
        $error = 'The passwords do not match.';
    } else {
        $profileCheck = mysqli_prepare(
            $conn,
            'SELECT 1 FROM `admin-info`
             WHERE email = (
                 SELECT email FROM staff_invitations
                 WHERE token_hash = ? AND accepted_at IS NULL AND expires_at > UTC_TIMESTAMP()
                 LIMIT 1
             )
             LIMIT 1'
        );
        if (!$profileCheck) {
            error_log('Staff invitation existing profile check could not be prepared: ' . mysqli_error($conn));
            $error = 'The invitation could not be checked. Please try again.';
        } else {
            $tokenHash = hash('sha256', $plainToken);
            mysqli_stmt_bind_param($profileCheck, 's', $tokenHash);
            if (!mysqli_stmt_execute($profileCheck)) {
                error_log('Staff invitation existing profile check failed: ' . mysqli_stmt_error($profileCheck));
                $error = 'The invitation could not be checked. Please try again.';
            } else {
                $profileResult = mysqli_stmt_get_result($profileCheck);
                if ($profileResult && mysqli_num_rows($profileResult) > 0) {
                    $error = 'A staff profile already exists for this email. Ask your organization administrator to resolve the existing profile before accepting this invitation.';
                }
            }
            mysqli_stmt_close($profileCheck);

            if ($error === '') {
                $tokenHash = hash('sha256', $plainToken);
                if (!mysqli_begin_transaction($conn)) {
                    error_log('Staff invitation acceptance transaction could not start: ' . mysqli_error($conn));
                    $error = 'The invitation could not be accepted right now. Please try again.';
                } else {
                    $lookup = mysqli_prepare(
                        $conn,
                        'SELECT id, org_id, first_name, last_name, email, phone, job_title, role
                         FROM staff_invitations
                         WHERE token_hash = ? AND accepted_at IS NULL AND expires_at > UTC_TIMESTAMP()
                         LIMIT 1 FOR UPDATE'
                    );
                    if (!$lookup) {
                        mysqli_rollback($conn);
                        error_log('Staff invitation lookup could not be prepared: ' . mysqli_error($conn));
                        $error = 'The invitation could not be checked. Please try again.';
                    } else {
                        mysqli_stmt_bind_param($lookup, 's', $tokenHash);
                        if (!mysqli_stmt_execute($lookup)) {
                            mysqli_rollback($conn);
                            error_log('Staff invitation lookup failed: ' . mysqli_stmt_error($lookup));
                            $error = 'The invitation could not be checked. Please try again.';
                        } else {
                            $result = mysqli_stmt_get_result($lookup);
                            $invitation = $result ? mysqli_fetch_assoc($result) : null;
                            mysqli_stmt_close($lookup);
                            if (!$invitation) {
                                mysqli_rollback($conn);
                                $error = 'This invitation has expired or has already been accepted.';
                            } else {
                                $validRoles = ['admin', 'manager', 'staff'];
                                if (!in_array($invitation['role'], $validRoles, true)) {
                                    mysqli_rollback($conn);
                                    error_log('Staff invitation contains an invalid role.');
                                    $error = 'This invitation cannot be accepted. Contact the organization administrator.';
                                } else {
                                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                                    if (!is_string($passwordHash)) {
                                        mysqli_rollback($conn);
                                        error_log('Staff invitation password hashing failed.');
                                        $error = 'Your password could not be saved. Please try again.';
                                    } else {
                                        $username = (string) $invitation['email'];
                                        $accountInsert = mysqli_prepare(
                                            $conn,
                                            "INSERT INTO `acc-info` (org_id, username, password, otp, status)
                                             VALUES (?, ?, ?, '', 'active')"
                                        );
                                        if (!$accountInsert) {
                                            mysqli_rollback($conn);
                                            error_log('Staff invitation account creation could not be prepared: ' . mysqli_error($conn));
                                            $error = 'The account could not be created. Contact the organization administrator.';
                                        } else {
                                            $orgId = (int) $invitation['org_id'];
                                            mysqli_stmt_bind_param($accountInsert, 'iss', $orgId, $username, $passwordHash);
                                            if (!mysqli_stmt_execute($accountInsert)) {
                                                mysqli_rollback($conn);
                                                error_log('Staff invitation account creation failed: ' . mysqli_stmt_error($accountInsert));
                                                $error = 'This email may already have an account. Contact the organization administrator.';
                                            } else {
                                                $accountId = mysqli_insert_id($conn);
                                                $profileInsert = mysqli_prepare(
                                                    $conn,
                                                    "INSERT INTO `admin-info`
                                                        (org_id, acc_id, `first-name`, `last-name`, email, phone, `job-title`, role, zone_id, subzone_id)
                                                     VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), ?, ?, NULL, NULL)"
                                                );
                                                if (!$profileInsert) {
                                                    mysqli_rollback($conn);
                                                    error_log('Staff invitation profile creation could not be prepared: ' . mysqli_error($conn));
                                                    $error = 'The account profile could not be created. Contact the organization administrator.';
                                                } else {
                                                    $firstName = (string) $invitation['first_name'];
                                                    $lastName = (string) $invitation['last_name'];
                                                    $email = (string) $invitation['email'];
                                                    $phone = (string) ($invitation['phone'] ?? '');
                                                    $jobTitle = (string) $invitation['job_title'];
                                                    $role = (string) $invitation['role'];
                                                    mysqli_stmt_bind_param(
                                                        $profileInsert,
                                                        'iissssss',
                                                        $orgId,
                                                        $accountId,
                                                        $firstName,
                                                        $lastName,
                                                        $email,
                                                        $phone,
                                                        $jobTitle,
                                                        $role
                                                    );
                                                    if (!mysqli_stmt_execute($profileInsert)) {
                                                        $profileErrorCode = mysqli_stmt_errno($profileInsert);
                                                        $profileError = mysqli_stmt_error($profileInsert);
                                                        mysqli_stmt_close($profileInsert);
                                                        mysqli_rollback($conn);
                                                        error_log('Staff invitation profile creation failed: ' . $profileError);
                                                        $error = $profileErrorCode === 1062
                                                            ? 'A staff profile already exists for this email. Ask your organization administrator to resolve the existing profile before accepting this invitation.'
                                                            : 'The account profile could not be created. Contact the organization administrator.';
                                                    } else {
                                                        mysqli_stmt_close($profileInsert);
                                                        $acceptUpdate = mysqli_prepare(
                                                            $conn,
                                                            'UPDATE staff_invitations SET accepted_at = UTC_TIMESTAMP()
                                                             WHERE id = ? AND accepted_at IS NULL'
                                                        );
                                                        if (!$acceptUpdate) {
                                                            mysqli_rollback($conn);
                                                            error_log('Staff invitation completion could not be prepared: ' . mysqli_error($conn));
                                                            $error = 'The invitation could not be completed. Please try again.';
                                                        } else {
                                                            $invitationId = (int) $invitation['id'];
                                                            mysqli_stmt_bind_param($acceptUpdate, 'i', $invitationId);
                                                            $accepted = mysqli_stmt_execute($acceptUpdate)
                                                                && mysqli_stmt_affected_rows($acceptUpdate) === 1;
                                                            mysqli_stmt_close($acceptUpdate);
                                                            if (!$accepted || !mysqli_commit($conn)) {
                                                                mysqli_rollback($conn);
                                                                error_log('Staff invitation completion could not commit: ' . mysqli_error($conn));
                                                                $error = 'The invitation could not be completed. Please try again.';
                                                            } else {
                                                                unset($_SESSION['staff_invitation_csrf']);
                                                                $success = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            mysqli_stmt_close($accountInsert);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
} else {
    $tokenHash = hash('sha256', $plainToken);
    $lookup = mysqli_prepare(
        $conn,
        'SELECT i.first_name, i.last_name, i.email, i.role, o.name AS organization_name
         FROM staff_invitations i
         INNER JOIN `org-info` o ON o.id = i.org_id
         WHERE i.token_hash = ? AND i.accepted_at IS NULL AND i.expires_at > UTC_TIMESTAMP()
         LIMIT 1'
    );
    if (!$lookup) {
        error_log('Staff invitation display lookup could not be prepared: ' . mysqli_error($conn));
        $error = 'The invitation could not be checked. Please try again later.';
    } else {
        mysqli_stmt_bind_param($lookup, 's', $tokenHash);
        if (!mysqli_stmt_execute($lookup)) {
            error_log('Staff invitation display lookup failed: ' . mysqli_stmt_error($lookup));
            $error = 'The invitation could not be checked. Please try again later.';
        } else {
            $result = mysqli_stmt_get_result($lookup);
            $invitation = $result ? mysqli_fetch_assoc($result) : null;
            if (!$invitation) {
                $error = 'This invitation has expired or has already been accepted.';
            }
        }
        mysqli_stmt_close($lookup);
    }
    $_SESSION['staff_invitation_csrf'] = bin2hex(random_bytes(32));
}

function invitationEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Set up your account - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/styles.css?v=20261009-password-toggle-1" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261009-invitation-1" />
    <style>
      body.invitation-page {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 2rem;
        background:
          radial-gradient(ellipse at 12% 8%, rgba(37, 99, 235, .08), transparent 34rem),
          #f5f7fb;
        color: #17243b;
        font-family: "Manrope", sans-serif;
      }
      .invitation-shell {
        display: grid;
        grid-template-columns: minmax(250px, .82fr) minmax(0, 1.18fr);
        width: min(100%, 900px);
        min-height: 540px;
        overflow: hidden;
        border: 1px solid #e8edf5;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(20, 40, 75, .12);
      }
      .invitation-aside {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2.5rem;
        background: linear-gradient(150deg, #102d57, #0a2244 72%);
        color: #fff;
      }
      .invitation-brand-logo { display: block; width: 112px; height: auto; }
      .invitation-aside-copy { margin: auto 0; padding: 3.5rem 0; }
      .invitation-aside-copy .eyebrow { margin-bottom: 1rem; color: #a9c8ff; font-size: .75rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
      .invitation-aside-copy h2 { margin: 0; color: #fff; font-size: clamp(1.8rem, 3vw, 2.35rem); font-weight: 700; line-height: 1.2; letter-spacing: -.04em; }
      .invitation-aside-copy p { margin-top: 1rem; color: #d1dced; font-size: .95rem; line-height: 1.75; }
      .invitation-aside-foot { color: #aebed3; font-size: .78rem; }
      .invitation-card { align-self: center; width: 100%; max-width: 520px; margin: 0 auto; padding: 3rem clamp(1.5rem, 5vw, 3.5rem); }
      .invitation-card .eyebrow { margin-bottom: .65rem; color: #2563eb; font-size: .76rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
      .invitation-card h1 { margin: 0; color: #10233f; font-size: clamp(1.7rem, 3vw, 2rem); font-weight: 800; line-height: 1.2; letter-spacing: -.04em; }
      .invitation-intro { margin: .85rem 0 1.5rem; color: #526176; font-size: .94rem; line-height: 1.7; }
      .invitation-email { margin-bottom: 1.5rem; padding: .85rem 1rem; border: 1px solid #e7edf6; border-radius: 10px; background: #f8faff; color: #243650; font-size: .88rem; overflow-wrap: anywhere; }
      .invitation-card label { display: block; margin: 1.1rem 0 .45rem; color: #26364d; font-size: .86rem; font-weight: 700; }
      .invitation-card input { width: 100%; min-height: 3rem; padding: .75rem .9rem; border: 1px solid #d7dfeb; border-radius: 9px; background: #fff; color: #17243b; font-family: inherit; font-size: .92rem; transition: border-color .15s ease, box-shadow .15s ease; }
      .invitation-card input:focus { border-color: #3975df; outline: 0; box-shadow: 0 0 0 3px rgba(57, 117, 223, .14); }
      .password-label-row { display: flex; align-items: center; gap: .4rem; }
      .password-label-row label { margin-bottom: .45rem; }
      .password-label-row .info-tip { margin-top: .65rem; }
      .invitation-card .password-field-wrapper-shared .password-visibility-toggle,
      .invitation-card .password-field-wrapper-shared .password-visibility-toggle:hover,
      .invitation-card .password-field-wrapper-shared .password-visibility-toggle:focus-visible { color: #64748b !important; }
      .invitation-card button[type="submit"] { width: 100%; min-height: 3.1rem; margin-top: 1.6rem; padding: .8rem 1rem; border: 0; border-radius: 9px; background: #1e56c5; color: #fff; font-family: inherit; font-size: .9rem; font-weight: 800; box-shadow: 0 6px 14px rgba(30, 86, 197, .2); transition: background .15s ease, transform .15s ease; }
      .invitation-card button[type="submit"]:hover { background: #1748a8; transform: translateY(-1px); }
      .invitation-notice { margin-top: 1.25rem; padding: .9rem 1rem; border: 1px solid #fecaca; border-radius: 9px; background: #fff5f5; color: #982b2b; font-size: .88rem; line-height: 1.6; }
      .invitation-notice.success { border-color: #bbebcb; background: #f0fdf4; color: #176b36; }
      .invitation-notice a { color: inherit; font-weight: 800; text-decoration: underline; text-underline-offset: 2px; }
      .invitation-expired { margin-top: 1.25rem; color: #526176; font-size: .9rem; line-height: 1.7; }
      @media (max-width: 700px) {
        body.invitation-page { padding: 1rem; }
        .invitation-shell { grid-template-columns: 1fr; min-height: 0; }
        .invitation-aside { min-height: 215px; padding: 1.5rem; }
        .invitation-aside-copy { padding: 2rem 0 1rem; }
        .invitation-aside-copy h2 { font-size: 1.65rem; }
        .invitation-aside-copy p, .invitation-aside-foot { display: none; }
        .invitation-card { padding: 2rem 1.5rem 2.25rem; }
      }
    </style>
  </head>
  <body class="invitation-page">
    <main class="invitation-shell">
      <aside class="invitation-aside" aria-label="Associa8 account invitation">
        <img class="invitation-brand-logo" src="../images/brand-logo-white.png" alt="Associa8" />
        <div class="invitation-aside-copy">
          <p class="eyebrow">Organization workspace</p>
          <h2>One account.<br />Your own access.</h2>
          <p>Set up your personal sign-in to join your organization's Associa8 workspace.</p>
        </div>
        <p class="invitation-aside-foot">Secure access for your organization</p>
      </aside>
      <section class="invitation-card">
      <?php if ($success): ?>
        <p class="eyebrow">Account ready</p>
        <h1>You're all set.</h1>
        <div class="invitation-notice success" role="status">
          Your account is ready. You can now <a href="index.php">sign in</a> with your email and the password you chose.
        </div>
      <?php elseif ($error !== ''): ?>
        <p class="eyebrow">Invitation</p>
        <h1>We couldn't open this invitation.</h1>
        <p class="invitation-notice" role="alert"><?php echo invitationEscape($error); ?></p>
        <p class="invitation-expired">Ask your organization administrator to send you a new invitation if this link has expired.</p>
      <?php elseif (is_array($invitation)): ?>
        <p class="eyebrow">Invitation accepted</p>
        <h1>Set up your account</h1>
        <p class="invitation-intro">You've been invited to join <strong><?php echo invitationEscape((string) $invitation['organization_name']); ?></strong>. Create a password to finish setting up your sign-in.</p>
        <p class="invitation-email"><strong>Sign-in email:</strong> <?php echo invitationEscape((string) $invitation['email']); ?></p>
        <form method="post" action="accept-staff-invitation.php">
          <input type="hidden" name="token" value="<?php echo invitationEscape($plainToken); ?>" />
          <input type="hidden" name="csrf_token" value="<?php echo invitationEscape((string) ($_SESSION['staff_invitation_csrf'] ?? '')); ?>" />
          <div class="password-label-row">
            <label for="password">Choose a password</label>
            <?php admin_info_tip('Use 12 to 72 characters. This password belongs to your sign-in, not to the organization.', 'Password help'); ?>
          </div>
          <input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required />
          <label for="password_confirmation">Confirm password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required />
          <button type="submit">Create my account</button>
        </form>
      <?php endif; ?>
      </section>
    </main>
    <script src="../js/password-visibility.js?v=20261009-password-toggle-2"></script>
  </body>
</html>
