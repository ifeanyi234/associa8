<?php
function admission_approval_requires_assignment(): bool
{
    return false;
}

function validate_title_assignment(
    mysqli $conn,
    int $orgId,
    int $titleId,
    ?int $zoneId,
    ?int $subzoneId,
    int $excludeMemberId = 0,
    ?string $scopeOverride = null
): array {
    $title = null;
    if ($titleId > 0) {
        $titleStatement = mysqli_prepare($conn, 'SELECT title, scope FROM titles WHERE id = ? AND org_id = ? LIMIT 1 FOR UPDATE');
        if (!$titleStatement) {
            error_log('Title assignment lookup prepare failed: ' . mysqli_error($conn));
            return ['valid' => false, 'message' => 'The selected title could not be checked.'];
        }
        mysqli_stmt_bind_param($titleStatement, 'ii', $titleId, $orgId);
        if (!mysqli_stmt_execute($titleStatement)) {
            error_log('Title assignment lookup failed: ' . mysqli_stmt_error($titleStatement));
            return ['valid' => false, 'message' => 'The selected title could not be checked.'];
        }
        $titleResult = mysqli_stmt_get_result($titleStatement);
        $title = $titleResult ? mysqli_fetch_assoc($titleResult) : null;
        if (!$title) {
            return ['valid' => false, 'message' => 'The selected title is not available in this Association.'];
        }
    }

    $zoneName = '';
    $subzoneName = '';

    if ($zoneId !== null && $zoneId > 0) {
        $zoneStatement = mysqli_prepare($conn, 'SELECT name FROM zones WHERE id = ? AND org_id = ? LIMIT 1');
        if (!$zoneStatement) {
            error_log('Title assignment zone prepare failed: ' . mysqli_error($conn));
            return ['valid' => false, 'message' => 'The selected zone could not be checked.'];
        }
        mysqli_stmt_bind_param($zoneStatement, 'ii', $zoneId, $orgId);
        if (!mysqli_stmt_execute($zoneStatement)) {
            error_log('Title assignment zone lookup failed: ' . mysqli_stmt_error($zoneStatement));
            return ['valid' => false, 'message' => 'The selected zone could not be checked.'];
        }
        $zoneResult = mysqli_stmt_get_result($zoneStatement);
        $zone = $zoneResult ? mysqli_fetch_assoc($zoneResult) : null;
        if (!$zone) {
            return ['valid' => false, 'message' => 'The selected zone is not available in this Association.'];
        }
        $zoneName = (string) $zone['name'];
    }

    if ($subzoneId !== null && $subzoneId > 0) {
        if ($zoneId === null || $zoneId < 1) {
            return ['valid' => false, 'message' => 'Choose a zone before assigning a sub-zone.'];
        }
        $subzoneStatement = mysqli_prepare($conn, 'SELECT sz.name FROM subzones sz INNER JOIN zones z ON z.id = sz.zone_id WHERE sz.id = ? AND sz.zone_id = ? AND z.org_id = ? LIMIT 1');
        if (!$subzoneStatement) {
            error_log('Title assignment sub-zone prepare failed: ' . mysqli_error($conn));
            return ['valid' => false, 'message' => 'The selected sub-zone could not be checked.'];
        }
        mysqli_stmt_bind_param($subzoneStatement, 'iii', $subzoneId, $zoneId, $orgId);
        if (!mysqli_stmt_execute($subzoneStatement)) {
            error_log('Title assignment sub-zone lookup failed: ' . mysqli_stmt_error($subzoneStatement));
            return ['valid' => false, 'message' => 'The selected sub-zone could not be checked.'];
        }
        $subzoneResult = mysqli_stmt_get_result($subzoneStatement);
        $subzone = $subzoneResult ? mysqli_fetch_assoc($subzoneResult) : null;
        if (!$subzone) {
            return ['valid' => false, 'message' => 'The selected sub-zone does not belong to the chosen zone in this Association.'];
        }
        $subzoneName = (string) $subzone['name'];
    }

    if ($title === null) {
        return ['valid' => true, 'message' => ''];
    }
    $scope = $scopeOverride ?? $title['scope'];
    if (!in_array($scope, ['hq', 'zone', 'subzone'], true)) {
        return ['valid' => false, 'message' => 'The selected title has an invalid level.'];
    }
    if ($scope === 'zone' && ($zoneId === null || $zoneId < 1)) {
        return ['valid' => false, 'message' => 'This title requires a zone assignment.'];
    }
    if ($scope === 'subzone' && ($subzoneId === null || $subzoneId < 1)) {
        return ['valid' => false, 'message' => 'This title requires a sub-zone assignment.'];
    }

    if ($scope === 'hq') {
        $holderSql = 'SELECT first_name, last_name FROM members WHERE org_id = ? AND title_id = ? AND id <> ? LIMIT 1 FOR UPDATE';
    } elseif ($scope === 'zone') {
        $holderSql = 'SELECT first_name, last_name FROM members WHERE org_id = ? AND title_id = ? AND zone_id = ? AND id <> ? LIMIT 1 FOR UPDATE';
    } else {
        $holderSql = 'SELECT first_name, last_name FROM members WHERE org_id = ? AND title_id = ? AND subzone_id = ? AND id <> ? LIMIT 1 FOR UPDATE';
    }
    $holderStatement = mysqli_prepare($conn, $holderSql);
    if (!$holderStatement) {
        error_log('Title assignment holder prepare failed: ' . mysqli_error($conn));
        return ['valid' => false, 'message' => 'The title availability could not be checked.'];
    }
    if ($scope === 'hq') {
        mysqli_stmt_bind_param($holderStatement, 'iii', $orgId, $titleId, $excludeMemberId);
    } elseif ($scope === 'zone') {
        $locationId = (int) $zoneId;
        mysqli_stmt_bind_param($holderStatement, 'iiii', $orgId, $titleId, $locationId, $excludeMemberId);
    } else {
        $locationId = (int) $subzoneId;
        mysqli_stmt_bind_param($holderStatement, 'iiii', $orgId, $titleId, $locationId, $excludeMemberId);
    }
    if (!mysqli_stmt_execute($holderStatement)) {
        error_log('Title assignment holder lookup failed: ' . mysqli_stmt_error($holderStatement));
        return ['valid' => false, 'message' => 'The title availability could not be checked.'];
    }
    $holderResult = mysqli_stmt_get_result($holderStatement);
    if (!$holderResult) {
        error_log('Title assignment holder result failed: ' . mysqli_stmt_error($holderStatement));
        return ['valid' => false, 'message' => 'The title availability could not be checked.'];
    }
    $holder = mysqli_fetch_assoc($holderResult);
    if ($holder) {
        $holderName = trim($holder['first_name'] . ' ' . $holder['last_name']);
        if ($scope === 'zone') {
            return ['valid' => false, 'message' => $title['title'] . ' is already held by ' . $holderName . ' in ' . $zoneName . ' Zone.'];
        }
        if ($scope === 'subzone') {
            return ['valid' => false, 'message' => $title['title'] . ' is already held by ' . $holderName . ' in ' . $subzoneName . '.'];
        }
        return ['valid' => false, 'message' => $title['title'] . ' is already held by ' . $holderName . ' in the Association.'];
    }

    return ['valid' => true, 'message' => ''];
}
