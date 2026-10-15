<?php
const MEMBER_REMEMBER_COOKIE = 'associa8_member_remember';
const MEMBER_REMEMBER_LIFETIME = 2592000;

function member_remember_cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function member_remember_cookie_parts(): ?array
{
    $value = $_COOKIE[MEMBER_REMEMBER_COOKIE] ?? '';
    if (!is_string($value) || !preg_match('/^([1-9][0-9]*)\.([a-f0-9]{64})$/D', $value, $matches)) {
        return null;
    }

    return [(int) $matches[1], $matches[2]];
}

function member_clear_remember_cookie(): bool
{
    $cleared = setcookie(MEMBER_REMEMBER_COOKIE, '', member_remember_cookie_options(time() - 3600));
    unset($_COOKIE[MEMBER_REMEMBER_COOKIE]);
    if (!$cleared) {
        error_log('Could not clear the member remember-me cookie; response headers may already have been sent.');
    }

    return $cleared;
}

function member_revoke_remember_token(mysqli $conn): bool
{
    $parts = member_remember_cookie_parts();
    $revoked = true;
    if ($parts !== null) {
        [$sessionId, $token] = $parts;
        $delete = mysqli_prepare($conn, 'DELETE FROM member_sessions WHERE id = ? AND token_hash = ?');
        if (!$delete) {
            error_log('Member remember-me token revocation could not be prepared: ' . mysqli_error($conn));
            $revoked = false;
        } else {
            $tokenHash = hash('sha256', $token);
            mysqli_stmt_bind_param($delete, 'is', $sessionId, $tokenHash);
            $revoked = mysqli_stmt_execute($delete);
            if (!$revoked) {
                error_log('Member remember-me token revocation failed: ' . mysqli_stmt_error($delete));
            }
            mysqli_stmt_close($delete);
        }
    }

    return member_clear_remember_cookie() && $revoked;
}

function member_create_remember_token(mysqli $conn, int $memberId): bool
{
    if (!member_revoke_remember_token($conn)) {
        return false;
    }

    try {
        $token = bin2hex(random_bytes(32));
    } catch (Throwable $error) {
        error_log('Member remember-me token generation failed: ' . $error->getMessage());
        return false;
    }

    $expiresAt = gmdate('Y-m-d H:i:s', time() + MEMBER_REMEMBER_LIFETIME);
    $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP) ? $ipAddress : null;
    $deviceLabel = 'Remembered browser';
    $insert = mysqli_prepare(
        $conn,
        'INSERT INTO member_sessions (member_id, device_label, ip_address, token_hash, expires_at)
         VALUES (?, ?, ?, ?, ?)'
    );
    if (!$insert) {
        error_log('Member remember-me token could not be prepared: ' . mysqli_error($conn));
        return false;
    }

    $tokenHash = hash('sha256', $token);
    mysqli_stmt_bind_param($insert, 'issss', $memberId, $deviceLabel, $ipAddress, $tokenHash, $expiresAt);
    $inserted = mysqli_stmt_execute($insert);
    if (!$inserted) {
        error_log('Member remember-me token could not be saved: ' . mysqli_stmt_error($insert));
        mysqli_stmt_close($insert);
        return false;
    }
    $sessionId = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($insert);

    $cookieValue = $sessionId . '.' . $token;
    $cookieSaved = setcookie(
        MEMBER_REMEMBER_COOKIE,
        $cookieValue,
        member_remember_cookie_options(time() + MEMBER_REMEMBER_LIFETIME)
    );
    if (!$cookieSaved) {
        error_log('Member remember-me cookie could not be set; response headers may already have been sent.');
        $delete = mysqli_prepare($conn, 'DELETE FROM member_sessions WHERE id = ? AND token_hash = ?');
        if ($delete) {
            mysqli_stmt_bind_param($delete, 'is', $sessionId, $tokenHash);
            mysqli_stmt_execute($delete);
            mysqli_stmt_close($delete);
        }
        return false;
    }

    return true;
}

function member_restore_remembered_session(mysqli $conn): bool
{
    $parts = member_remember_cookie_parts();
    if ($parts === null) {
        if (isset($_COOKIE[MEMBER_REMEMBER_COOKIE])) {
            member_clear_remember_cookie();
        }
        return false;
    }

    [$sessionId, $token] = $parts;
    $tokenHash = hash('sha256', $token);
    $lookup = mysqli_prepare(
        $conn,
        "SELECT ms.member_id, m.org_id, m.zone_id, m.subzone_id, m.first_name, m.last_name
         FROM member_sessions ms
         INNER JOIN members m ON m.id = ms.member_id
         WHERE ms.id = ? AND ms.token_hash = ? AND ms.expires_at > UTC_TIMESTAMP()
           AND COALESCE(m.status, 'active') <> 'suspended'
         LIMIT 1"
    );
    if (!$lookup) {
        error_log('Member remember-me lookup could not be prepared: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($lookup, 'is', $sessionId, $tokenHash);
    if (!mysqli_stmt_execute($lookup)) {
        error_log('Member remember-me lookup failed: ' . mysqli_stmt_error($lookup));
        mysqli_stmt_close($lookup);
        return false;
    }
    $result = mysqli_stmt_get_result($lookup);
    $member = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($lookup);
    if (!$member) {
        member_clear_remember_cookie();
        return false;
    }

    $touch = mysqli_prepare($conn, 'UPDATE member_sessions SET last_active_at = UTC_TIMESTAMP() WHERE id = ?');
    if (!$touch) {
        error_log('Member remember-me activity update could not be prepared: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($touch, 'i', $sessionId);
    $touched = mysqli_stmt_execute($touch);
    if (!$touched) {
        error_log('Member remember-me activity update failed: ' . mysqli_stmt_error($touch));
    }
    mysqli_stmt_close($touch);
    if (!$touched) {
        return false;
    }

    if (!session_regenerate_id(true)) {
        error_log('Could not regenerate the PHP session after member remember-me authentication.');
        return false;
    }
    $_SESSION['member_id'] = (int) $member['member_id'];
    $_SESSION['member_org_id'] = isset($member['org_id']) ? (int) $member['org_id'] : null;
    $_SESSION['member_zone_id'] = isset($member['zone_id']) ? (int) $member['zone_id'] : null;
    $_SESSION['member_subzone_id'] = isset($member['subzone_id']) ? (int) $member['subzone_id'] : null;
    $_SESSION['member_name'] = trim((string) $member['first_name'] . ' ' . (string) $member['last_name']);

    return true;
}
