<?php
const ADMIN_REMEMBER_COOKIE = 'associa8_admin_remember';
const ADMIN_REMEMBER_LIFETIME = 2592000;

function admin_remember_cookie_options(int $expires): array
{
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $applicationPath = dirname(dirname($scriptName));
    if ($applicationPath === '.' || $applicationPath === DIRECTORY_SEPARATOR) {
        $applicationPath = '';
    }

    return [
        'expires' => $expires,
        'path' => rtrim(str_replace('\\', '/', $applicationPath), '/') . '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function admin_remember_cookie_parts(): ?array
{
    $value = $_COOKIE[ADMIN_REMEMBER_COOKIE] ?? '';
    if (!is_string($value) || !preg_match('/^([1-9][0-9]*)\.([a-f0-9]{64})$/D', $value, $matches)) {
        return null;
    }

    return [(int) $matches[1], $matches[2]];
}

function admin_clear_remember_cookie(): bool
{
    $cleared = setcookie(ADMIN_REMEMBER_COOKIE, '', admin_remember_cookie_options(time() - 3600));
    unset($_COOKIE[ADMIN_REMEMBER_COOKIE]);
    if (!$cleared) {
        error_log('Could not clear the admin remember-me cookie; response headers may already have been sent.');
    }

    return $cleared;
}

function admin_revoke_remember_token(mysqli $conn): bool
{
    $parts = admin_remember_cookie_parts();
    $revoked = true;
    if ($parts !== null) {
        [$tokenId, $token] = $parts;
        $delete = mysqli_prepare($conn, 'DELETE FROM admin_remember_tokens WHERE id = ? AND token_hash = ?');
        if (!$delete) {
            error_log('Admin remember-me token revocation could not be prepared: ' . mysqli_error($conn));
            $revoked = false;
        } else {
            $tokenHash = hash('sha256', $token);
            mysqli_stmt_bind_param($delete, 'is', $tokenId, $tokenHash);
            $revoked = mysqli_stmt_execute($delete);
            if (!$revoked) {
                error_log('Admin remember-me token revocation failed: ' . mysqli_stmt_error($delete));
            }
            mysqli_stmt_close($delete);
        }
    }

    return admin_clear_remember_cookie() && $revoked;
}

function admin_create_remember_token(mysqli $conn, int $accountId): bool
{
    if (!admin_revoke_remember_token($conn)) {
        return false;
    }

    try {
        $token = bin2hex(random_bytes(32));
    } catch (Throwable $error) {
        error_log('Admin remember-me token generation failed: ' . $error->getMessage());
        return false;
    }

    $tokenHash = hash('sha256', $token);
    $expiresAt = gmdate('Y-m-d H:i:s', time() + ADMIN_REMEMBER_LIFETIME);
    $insert = mysqli_prepare(
        $conn,
        'INSERT INTO admin_remember_tokens (account_id, token_hash, expires_at)
         VALUES (?, ?, ?)'
    );
    if (!$insert) {
        error_log('Admin remember-me token could not be prepared: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($insert, 'iss', $accountId, $tokenHash, $expiresAt);
    $inserted = mysqli_stmt_execute($insert);
    if (!$inserted) {
        error_log('Admin remember-me token could not be saved: ' . mysqli_stmt_error($insert));
        mysqli_stmt_close($insert);
        return false;
    }
    $tokenId = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($insert);

    $cookieSaved = setcookie(
        ADMIN_REMEMBER_COOKIE,
        $tokenId . '.' . $token,
        admin_remember_cookie_options(time() + ADMIN_REMEMBER_LIFETIME)
    );
    if (!$cookieSaved) {
        error_log('Admin remember-me cookie could not be set; response headers may already have been sent.');
        $delete = mysqli_prepare($conn, 'DELETE FROM admin_remember_tokens WHERE id = ? AND token_hash = ?');
        if ($delete) {
            mysqli_stmt_bind_param($delete, 'is', $tokenId, $tokenHash);
            mysqli_stmt_execute($delete);
            mysqli_stmt_close($delete);
        }
        return false;
    }

    return true;
}

function admin_restore_remembered_session(mysqli $conn): bool
{
    $parts = admin_remember_cookie_parts();
    if ($parts === null) {
        if (isset($_COOKIE[ADMIN_REMEMBER_COOKIE])) {
            admin_clear_remember_cookie();
        }
        return false;
    }

    [$tokenId, $token] = $parts;
    $tokenHash = hash('sha256', $token);
    $lookup = mysqli_prepare(
        $conn,
        "SELECT a.id, a.org_id, a.username, ai.role, ai.zone_id, ai.subzone_id,
                ai.`first-name` AS first_name, ai.`last-name` AS last_name, o.name AS organization_name
         FROM admin_remember_tokens t
         INNER JOIN `acc-info` a ON a.id = t.account_id AND a.status = 'active'
         INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
         LEFT JOIN `org-info` o ON o.id = a.org_id
         WHERE t.id = ? AND t.token_hash = ? AND t.expires_at > UTC_TIMESTAMP()
         LIMIT 1"
    );
    if (!$lookup) {
        error_log('Admin remember-me lookup could not be prepared: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($lookup, 'is', $tokenId, $tokenHash);
    if (!mysqli_stmt_execute($lookup)) {
        error_log('Admin remember-me lookup failed: ' . mysqli_stmt_error($lookup));
        mysqli_stmt_close($lookup);
        return false;
    }
    $result = mysqli_stmt_get_result($lookup);
    $account = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($lookup);
    $validRoles = ['super_admin', 'admin', 'zonal_admin', 'subzonal_admin'];
    if (
        !$account
        || !in_array($account['role'], $validRoles, true)
        || (int) $account['org_id'] < 1
    ) {
        admin_clear_remember_cookie();
        return false;
    }

    $touch = mysqli_prepare($conn, 'UPDATE admin_remember_tokens SET last_active_at = UTC_TIMESTAMP() WHERE id = ?');
    if (!$touch) {
        error_log('Admin remember-me activity update could not be prepared: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($touch, 'i', $tokenId);
    $touched = mysqli_stmt_execute($touch);
    if (!$touched) {
        error_log('Admin remember-me activity update failed: ' . mysqli_stmt_error($touch));
    }
    mysqli_stmt_close($touch);
    if (!$touched) {
        return false;
    }

    if (!session_regenerate_id(true)) {
        error_log('Could not regenerate the PHP session after admin remember-me authentication.');
        return false;
    }
    $_SESSION['user_id'] = (int) $account['id'];
    $_SESSION['username'] = (string) $account['username'];
    $_SESSION['org_id'] = (int) $account['org_id'];
    $_SESSION['admin_is_super_admin'] = $account['role'] === 'super_admin';
    $_SESSION['admin_display_role'] = (string) $account['role'];
    $_SESSION['admin_role'] = $account['role'] === 'super_admin' ? 'admin' : (string) $account['role'];
    $_SESSION['admin_zone_id'] = isset($account['zone_id']) ? (int) $account['zone_id'] : null;
    $_SESSION['admin_subzone_id'] = isset($account['subzone_id']) ? (int) $account['subzone_id'] : null;
    $_SESSION['admin_name'] = trim((string) $account['first_name'] . ' ' . (string) $account['last_name']);
    $_SESSION['admin_organization_name'] = trim((string) ($account['organization_name'] ?? ''));

    return true;
}
