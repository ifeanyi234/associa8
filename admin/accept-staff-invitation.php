<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/db.php';

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
                                                mysqli_rollback($conn);
                                                error_log('Staff invitation profile creation failed: ' . mysqli_stmt_error($profileInsert));
                                                $error = 'The account profile could not be created. Contact the organization administrator.';
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
                                    if ($accountInsert) {
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
    <title>Accept organization invitation - Associa8</title>
    <link rel="stylesheet" href="../css/styles.css" />
    <style>
      body { min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; background: #f4f7fb; }
      .invitation-card { width: min(100%, 480px); padding: 2rem; background: #fff; border-radius: 14px; box-shadow: 0 12px 40px rgba(10, 34, 68, .12); }
      .invitation-card h1 { margin-top: 0; color: #0a2244; }
      .invitation-card label { display: block; margin: 1rem 0 .35rem; font-weight: 600; }
      .invitation-card input { width: 100%; padding: .8rem; border: 1px solid #cbd5e1; border-radius: 8px; }
      .invitation-card button { width: 100%; margin-top: 1.25rem; padding: .85rem; border: 0; border-radius: 8px; background: #0a2244; color: #fff; font-weight: 700; cursor: pointer; }
      .invitation-notice { padding: .85rem 1rem; border-radius: 8px; background: #fff1f0; color: #8f1d18; }
      .invitation-notice.success { background: #ecfdf3; color: #166534; }
    </style>
  </head>
  <body>
    <main class="invitation-card">
      <h1>Accept your invitation</h1>
      <?php if ($success): ?>
        <div class="invitation-notice success" role="status">
          Your account is ready. You can now <a href="index.php">sign in</a> with your email and the password you chose.
        </div>
      <?php elseif ($error !== ''): ?>
        <p class="invitation-notice" role="alert"><?php echo invitationEscape($error); ?></p>
      <?php elseif (is_array($invitation)): ?>
        <p>You have been invited to join <strong><?php echo invitationEscape((string) $invitation['organization_name']); ?></strong>.</p>
        <p><strong>Your assigned role:</strong> <?php echo invitationEscape(ucfirst(str_replace('_', ' ', (string) $invitation['role']))); ?>. This access level was set by the organization administrator.</p>
        <p>Sign-in email: <strong><?php echo invitationEscape((string) $invitation['email']); ?></strong></p>
        <form method="post" action="accept-staff-invitation.php">
          <input type="hidden" name="token" value="<?php echo invitationEscape($plainToken); ?>" />
          <input type="hidden" name="csrf_token" value="<?php echo invitationEscape((string) ($_SESSION['staff_invitation_csrf'] ?? '')); ?>" />
          <label for="password">Choose a password</label>
          <input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required />
          <label for="password_confirmation">Confirm password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required />
          <p>Use 12 to 72 characters. This password belongs to your sign-in, not to the organization.</p>
          <button type="submit">Accept invitation and set password</button>
        </form>
      <?php endif; ?>
    </main>
  </body>
</html>
