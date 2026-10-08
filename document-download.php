<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/document-file.php';

$documentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$documentId || $documentId < 1) {
    http_response_code(404);
    exit('Document not found.');
}

$sql = 'SELECT d.file_path, d.title, d.file_type FROM documents d WHERE d.id = ?';
$types = 'i';
$parameters = [$documentId];

if (isset($_SESSION['user_id'], $_SESSION['admin_role']) && ctype_digit((string) $_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0) {
    $role = $_SESSION['admin_role'];
    if (!is_string($role) || !in_array($role, ['super_admin', 'admin', 'manager', 'staff'], true)) {
        http_response_code(403);
        exit('Access denied.');
    }

    if ($role !== 'super_admin') {
        $orgId = filter_var($_SESSION['org_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$orgId || $orgId < 1) {
            http_response_code(403);
            exit('Access denied.');
        }
        $sql .= ' AND d.org_id = ?';
        $types .= 'i';
        $parameters[] = $orgId;

        $zoneId = filter_var($_SESSION['admin_zone_id'] ?? null, FILTER_VALIDATE_INT);
        if ($zoneId && $zoneId > 0) {
            $subzoneId = filter_var($_SESSION['admin_subzone_id'] ?? null, FILTER_VALIDATE_INT);
            $sql .= ' AND ((d.zone_id IS NULL AND d.subzone_id IS NULL) OR d.zone_id = ? OR d.subzone_id = ?)';
            $types .= 'ii';
            $parameters[] = $zoneId;
            $parameters[] = $subzoneId && $subzoneId > 0 ? $subzoneId : 0;
        }
    }
} elseif (isset($_SESSION['member_id'], $_SESSION['member_org_id'])) {
    $memberId = filter_var($_SESSION['member_id'], FILTER_VALIDATE_INT);
    $orgId = filter_var($_SESSION['member_org_id'], FILTER_VALIDATE_INT);
    if (!$memberId || !$orgId || $memberId < 1 || $orgId < 1) {
        http_response_code(403);
        exit('Access denied.');
    }

    $memberQuery = mysqli_prepare($conn, 'SELECT zone_id, subzone_id FROM members WHERE id = ? AND org_id = ? AND status <> ? LIMIT 1');
    $blockedStatus = 'suspended';
    if (!$memberQuery) {
        error_log('Document access member query prepare failed: ' . mysqli_error($conn));
        http_response_code(500);
        exit('Document is unavailable.');
    }
    mysqli_stmt_bind_param($memberQuery, 'iis', $memberId, $orgId, $blockedStatus);
    if (!mysqli_stmt_execute($memberQuery)) {
        error_log('Document access member query failed: ' . mysqli_stmt_error($memberQuery));
        http_response_code(500);
        exit('Document is unavailable.');
    }
    $memberResult = mysqli_stmt_get_result($memberQuery);
    $member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;
    if (!$member) {
        http_response_code(403);
        exit('Access denied.');
    }

    $sql .= ' AND d.org_id = ? AND ((d.zone_id IS NULL AND d.subzone_id IS NULL) OR d.zone_id = ? OR d.subzone_id = ?)';
    $types .= 'iii';
    $parameters[] = $orgId;
    $parameters[] = (int) ($member['zone_id'] ?? 0);
    $parameters[] = (int) ($member['subzone_id'] ?? 0);
} else {
    http_response_code(403);
    exit('Access denied.');
}

$statement = mysqli_prepare($conn, $sql);
if (!$statement) {
    error_log('Document query prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Document is unavailable.');
}
mysqli_stmt_bind_param($statement, $types, ...$parameters);
if (!mysqli_stmt_execute($statement)) {
    error_log('Document query failed: ' . mysqli_stmt_error($statement));
    http_response_code(500);
    exit('Document is unavailable.');
}
$result = mysqli_stmt_get_result($statement);
$document = $result ? mysqli_fetch_assoc($result) : null;
$filePath = $document ? associa8_resolve_document_file($document['file_path']) : null;
if (!$document || $filePath === null) {
    http_response_code(404);
    exit('Document not found.');
}

$mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . (string) filesize($filePath));
header('Content-Disposition: inline; filename="' . rawurlencode(basename($filePath)) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($filePath);
exit;
