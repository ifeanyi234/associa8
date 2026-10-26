<?php
function admin_scope_context(mysqli $conn): array
{
    $role = (string) ($_SESSION['admin_role'] ?? '');
    $orgId = (int) ($_SESSION['org_id'] ?? 0);
    $context = [
        'valid' => true,
        'role' => $role,
        'org_id' => $orgId,
        'zone_ids' => [],
        'subzone_ids' => [],
        'zone_id' => null,
        'subzone_id' => null,
        'is_global' => !in_array($role, ['zonal_admin', 'subzonal_admin'], true),
    ];

    if ($orgId < 1) {
        $context['valid'] = false;
        return $context;
    }
    if ($context['is_global']) {
        return $context;
    }

    if ($role === 'zonal_admin') {
        $zoneId = (int) ($_SESSION['admin_zone_id'] ?? 0);
        $zoneStatement = mysqli_prepare($conn, 'SELECT id FROM zones WHERE id = ? AND org_id = ? LIMIT 1');
        if (!$zoneStatement) {
            error_log('Admin scope zone check could not be prepared: ' . mysqli_error($conn));
            $context['valid'] = false;
            return $context;
        }
        mysqli_stmt_bind_param($zoneStatement, 'ii', $zoneId, $orgId);
        if (!mysqli_stmt_execute($zoneStatement)) {
            error_log('Admin scope zone check failed: ' . mysqli_stmt_error($zoneStatement));
            mysqli_stmt_close($zoneStatement);
            $context['valid'] = false;
            return $context;
        }
        $zoneResult = mysqli_stmt_get_result($zoneStatement);
        $zone = $zoneResult ? mysqli_fetch_assoc($zoneResult) : null;
        mysqli_stmt_close($zoneStatement);
        if (!$zone) {
            $context['valid'] = false;
            return $context;
        }
        $context['zone_id'] = $zoneId;
        $context['zone_ids'] = [$zoneId];
    } elseif ($role === 'subzonal_admin') {
        $subzoneId = (int) ($_SESSION['admin_subzone_id'] ?? 0);
        $subzoneStatement = mysqli_prepare(
            $conn,
            'SELECT sz.id, sz.zone_id FROM subzones sz
             INNER JOIN zones z ON z.id = sz.zone_id
             WHERE sz.id = ? AND z.org_id = ? LIMIT 1'
        );
        if (!$subzoneStatement) {
            error_log('Admin scope sub-zone check could not be prepared: ' . mysqli_error($conn));
            $context['valid'] = false;
            return $context;
        }
        mysqli_stmt_bind_param($subzoneStatement, 'ii', $subzoneId, $orgId);
        if (!mysqli_stmt_execute($subzoneStatement)) {
            error_log('Admin scope sub-zone check failed: ' . mysqli_stmt_error($subzoneStatement));
            mysqli_stmt_close($subzoneStatement);
            $context['valid'] = false;
            return $context;
        }
        $subzoneResult = mysqli_stmt_get_result($subzoneStatement);
        $subzone = $subzoneResult ? mysqli_fetch_assoc($subzoneResult) : null;
        mysqli_stmt_close($subzoneStatement);
        if (!$subzone) {
            $context['valid'] = false;
            return $context;
        }
        $context['subzone_id'] = $subzoneId;
        $context['zone_id'] = (int) $subzone['zone_id'];
        $context['zone_ids'] = [(int) $subzone['zone_id']];
        $context['subzone_ids'] = [$subzoneId];
    } else {
        $context['valid'] = false;
        return $context;
    }

    if ($role === 'zonal_admin') {
        $subzoneStatement = mysqli_prepare($conn, 'SELECT id FROM subzones WHERE zone_id = ? ORDER BY id');
        if (!$subzoneStatement) {
            error_log('Admin scope sub-zone list could not be prepared: ' . mysqli_error($conn));
            $context['valid'] = false;
            return $context;
        }
        $zoneId = (int) $context['zone_id'];
        mysqli_stmt_bind_param($subzoneStatement, 'i', $zoneId);
        if (!mysqli_stmt_execute($subzoneStatement)) {
            error_log('Admin scope sub-zone list failed: ' . mysqli_stmt_error($subzoneStatement));
            mysqli_stmt_close($subzoneStatement);
            $context['valid'] = false;
            return $context;
        }
        $subzoneResult = mysqli_stmt_get_result($subzoneStatement);
        if (!$subzoneResult) {
            mysqli_stmt_close($subzoneStatement);
            $context['valid'] = false;
            return $context;
        }
        while ($subzone = mysqli_fetch_assoc($subzoneResult)) {
            $context['subzone_ids'][] = (int) $subzone['id'];
        }
        mysqli_stmt_close($subzoneStatement);
    }

    return $context;
}

function admin_scope_sql(string $alias, array $context, string $zoneColumn = 'zone_id', string $subzoneColumn = 'subzone_id'): string
{
    foreach ([$alias, $zoneColumn, $subzoneColumn] as $identifier) {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException('Invalid SQL identifier in admin scope.');
        }
    }
    if (empty($context['valid'])) {
        return ' AND 1 = 0';
    }
    $orgId = (int) ($context['org_id'] ?? 0);
    if ($orgId < 1) {
        return ' AND 1 = 0';
    }
    $organizationSql = "{$alias}.org_id = {$orgId}";
    if (!empty($context['is_global'])) {
        return " AND {$organizationSql}";
    }

    $subzoneIds = array_values(array_unique(array_filter(array_map('intval', $context['subzone_ids'] ?? []), static fn (int $id): bool => $id > 0)));
    if (($context['role'] ?? '') === 'subzonal_admin') {
        $subzoneId = (int) ($context['subzone_id'] ?? 0);
        $zoneId = (int) ($context['zone_id'] ?? 0);
        return $subzoneId > 0 && $zoneId > 0
            ? " AND {$organizationSql} AND {$alias}.{$subzoneColumn} = {$subzoneId} AND {$alias}.{$zoneColumn} = {$zoneId}"
            : ' AND 1 = 0';
    }
    $zoneId = (int) ($context['zone_id'] ?? 0);
    if ($zoneId < 1) {
        return ' AND 1 = 0';
    }
    $locationSql = "{$alias}.{$zoneColumn} = {$zoneId} AND {$alias}.{$subzoneColumn} IS NULL";
    if ($subzoneIds) {
        $subzoneList = implode(',', $subzoneIds);
        $locationSql = "{$alias}.{$zoneColumn} = {$zoneId} AND ({$alias}.{$subzoneColumn} IS NULL OR {$alias}.{$subzoneColumn} IN ({$subzoneList}))";
    }
    return " AND {$organizationSql} AND ({$locationSql})";
}

function admin_scope_allows_member(array $context, array $member): bool
{
    if (empty($context['valid'])) {
        return false;
    }
    if ((int) ($member['org_id'] ?? 0) !== (int) ($context['org_id'] ?? 0)) {
        return false;
    }
    if (($context['role'] ?? '') === 'super_admin') {
        return true;
    }
    if (!empty($context['is_global'])) {
        return true;
    }
    if (($context['role'] ?? '') === 'zonal_admin') {
        $zoneMatches = (int) ($member['zone_id'] ?? 0) === (int) ($context['zone_id'] ?? 0);
        $subzoneId = (int) ($member['subzone_id'] ?? 0);
        $subzoneMatches = $subzoneId > 0 && in_array($subzoneId, $context['subzone_ids'] ?? [], true);
        return $zoneMatches && ($subzoneId === 0 || $subzoneMatches);
    }
    return ($context['role'] ?? '') === 'subzonal_admin'
        && (int) ($member['subzone_id'] ?? 0) === (int) ($context['subzone_id'] ?? 0)
        && (int) ($member['zone_id'] ?? 0) === (int) ($context['zone_id'] ?? 0);
}
