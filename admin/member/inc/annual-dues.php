<?php

function member_load_annual_dues(mysqli $conn, int $memberId, int $orgId): ?array
{
    $lagosNow = new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos'));
    $yearStartLagos = $lagosNow->setDate((int) $lagosNow->format('Y'), 1, 1)->setTime(0, 0);
    $yearEndLagos = $yearStartLagos->modify('+1 year');
    $utc = new DateTimeZone('UTC');
    $yearStartUtc = $yearStartLagos->setTimezone($utc)->format('Y-m-d H:i:s');
    $yearEndUtc = $yearEndLagos->setTimezone($utc)->format('Y-m-d H:i:s');

    $statement = mysqli_prepare(
        $conn,
        "SELECT COALESCE(z.annual_dues, 0) + COALESCE(sz.annual_dues, 0) AS total_due,
                (
                    SELECT COALESCE(SUM(ft.amount), 0)
                    FROM finance_transactions ft
                    WHERE ft.member_id = m.id
                      AND ft.type = 'membership_dues'
                      AND ft.status = 'successful'
                      AND ft.paid_at >= ?
                      AND ft.paid_at < ?
                ) AS paid_this_year
         FROM members m
         LEFT JOIN zones z ON z.id = m.zone_id AND z.org_id = m.org_id
         LEFT JOIN subzones sz ON sz.id = m.subzone_id AND sz.zone_id = m.zone_id
         WHERE m.id = ? AND m.org_id = ?
         LIMIT 1"
    );
    if (!$statement) {
        error_log('Member annual dues lookup could not be prepared: ' . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($statement, 'ssii', $yearStartUtc, $yearEndUtc, $memberId, $orgId);
    if (!mysqli_stmt_execute($statement)) {
        error_log('Member annual dues lookup failed: ' . mysqli_stmt_error($statement));
        mysqli_stmt_close($statement);
        return null;
    }

    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($statement);
    if (!$row) {
        error_log('Member annual dues lookup returned no member row.');
        return null;
    }

    $totalDue = (float) $row['total_due'];
    $paid = (float) $row['paid_this_year'];
    return [
        'year' => $yearStartLagos->format('Y'),
        'total_due' => $totalDue,
        'paid' => $paid,
        'outstanding' => max(0.0, $totalDue - $paid),
    ];
}
