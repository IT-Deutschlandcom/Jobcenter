<?php
require_once __DIR__ . '/promotion_service.php';

function payroll_rate_cents($rankId)
{
    if ($rankId >= 1 && $rankId <= 49) {
        return 250;
    }
    return $rankId >= 50 && $rankId <= 75 ? 300 : 0;
}

function work_summary($user, $now)
{
    $total = (int) $user['total_seconds'] + earned_seconds($user, $now);
    $unpaid = max(0, $total - (int) $user['paid_seconds']);
    $hours = intdiv($unpaid, PROMOTION_INTERVAL);
    $employeeRank = (int) $user['rank_id'] >= 1 && (int) $user['rank_id'] <= 49;
    $rate = payroll_rate_cents((int) $user['rank_id']);
    $paidCents = isset($user['id'])
        ? (int) round((float) service_query('SELECT COALESCE(SUM(taler), 0) FROM payouts WHERE user_id = ?', [$user['id']])->fetchColumn() * 100)
        : 0;
    return [
        'total_seconds' => $total,
        'unpaid_seconds' => $unpaid,
        'payable_hours' => $employeeRank ? $hours : 0,
        'payable_cents' => $employeeRank ? $hours * $rate : 0,
        'paid_cents' => $paidCents,
    ];
}

function format_taler($cents)
{
    return number_format(((int) $cents) / 100, 2, ',', '.') . ' Taler';
}

function duration_label($seconds)
{
    return intdiv((int) $seconds, 3600) . ' h ' . intdiv((int) $seconds % 3600, 60) . ' min';
}

function active_payroll_period($rankId)
{
    return service_query('SELECT * FROM payroll_periods WHERE rank_id = ? AND ended_at IS NULL', [(int) $rankId])->fetch();
}

function payroll_period_summary($periodId, $userId = null)
{
    $period = service_query('SELECT * FROM payroll_periods WHERE id = ?', [(int) $periodId])->fetch();
    if (!$period) {
        return [];
    }
    $sql = 'SELECT m.*, u.username, u.rank_id, u.rank_step, u.total_seconds, u.active_since, u.paid_seconds AS lifetime_paid_seconds
        FROM payroll_period_members m JOIN users u ON u.id = m.user_id WHERE m.period_id = ?';
    $params = [(int) $periodId];
    if ($userId !== null) {
        $sql .= ' AND m.user_id = ?';
        $params[] = (int) $userId;
    }
    $rows = service_query($sql . ' ORDER BY u.username', $params)->fetchAll();
    foreach ($rows as &$row) {
        $finalSeconds = $period['ended_at'] === null
            ? (int) $row['total_seconds'] + earned_seconds($row, time())
            : (int) $row['final_total_seconds'];
        $row['period_seconds'] = max(0, $finalSeconds - (int) $row['baseline_seconds']);
        $row['unpaid_seconds'] = max(0, $row['period_seconds'] + (int) $row['adjustment_seconds'] - (int) $row['paid_seconds']);
        $row['payable_hours'] = intdiv($row['unpaid_seconds'], PROMOTION_INTERVAL);
        $row['rate_cents'] = payroll_rate_cents((int) $period['rank_id']);
        $row['payable_cents'] = $row['payable_hours'] * $row['rate_cents'];
        $row['paid_cents'] = (int) round((float) service_query(
            'SELECT COALESCE(SUM(taler), 0) FROM payouts WHERE period_id = ? AND user_id = ?',
            [$periodId, $row['user_id']]
        )->fetchColumn() * 100);
    }
    unset($row);
    return ['period' => $period, 'members' => $rows];
}

function start_payroll_period($actorId, $rankId)
{
    $rankId = (int) $rankId;
    if ($rankId < 1 || $rankId > 49) {
        throw new DomainException('Lohnperioden können nur für die Ränge 1 bis 49 gestartet werden.');
    }
    $actor = service_query('SELECT * FROM users WHERE id = ?', [(int) $actorId])->fetch();
    if (!$actor || !can_manage_payroll_periods($actor)) {
        throw new DomainException('Lohnperioden dürfen erst ab Rang 65 verwaltet werden.');
    }
    synchronize_visible_users($actor);
    db()->beginTransaction();
    try {
        $currentActor = service_query('SELECT * FROM users WHERE id = ? FOR UPDATE', [(int) $actorId])->fetch();
        if (!$currentActor || !can_manage_payroll_periods($currentActor)) {
            throw new DomainException('Lohnperioden dürfen erst ab Rang 65 verwaltet werden.');
        }
        if (active_payroll_period($rankId)) {
            throw new DomainException('Für diesen Rang läuft bereits eine Lohnperiode.');
        }
        $startedAt = time();
        service_query('INSERT INTO payroll_periods (rank_id, started_by, started_at) VALUES (?, ?, ?)',
            [$rankId, (int) $actorId, $startedAt]);
        $periodId = (int) db()->lastInsertId();
        $members = service_query('SELECT id, total_seconds FROM users WHERE rank_id = ? ORDER BY id', [$rankId])->fetchAll();
        foreach ($members as $member) {
            service_query('INSERT INTO payroll_period_members (period_id, user_id, baseline_seconds) VALUES (?, ?, ?)',
                [$periodId, $member['id'], $member['total_seconds']]);
        }
        db()->commit();
        return $periodId;
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function end_payroll_period($actorId, $periodId)
{
    $period = service_query('SELECT * FROM payroll_periods WHERE id = ?', [(int) $periodId])->fetch();
    if (!$period || $period['ended_at'] !== null) {
        throw new DomainException('Die Lohnperiode ist nicht aktiv.');
    }
    foreach (service_query('SELECT user_id FROM payroll_period_members WHERE period_id = ?', [(int) $periodId])->fetchAll(PDO::FETCH_COLUMN) as $userId) {
        synchronize_user($userId);
    }
    db()->beginTransaction();
    try {
        $actor = service_query('SELECT * FROM users WHERE id = ? FOR UPDATE', [(int) $actorId])->fetch();
        if (!$actor || !can_manage_payroll_periods($actor)) {
            throw new DomainException('Lohnperioden dürfen erst ab Rang 65 verwaltet werden.');
        }
        $period = service_query('SELECT * FROM payroll_periods WHERE id = ? FOR UPDATE', [(int) $periodId])->fetch();
        if (!$period || $period['ended_at'] !== null) {
            throw new DomainException('Die Lohnperiode ist nicht aktiv.');
        }
        $endedAt = time();
        $members = service_query('SELECT m.user_id, u.total_seconds FROM payroll_period_members m JOIN users u ON u.id = m.user_id WHERE m.period_id = ?',
            [(int) $periodId])->fetchAll();
        foreach ($members as $member) {
            service_query('UPDATE payroll_period_members SET final_total_seconds = ? WHERE period_id = ? AND user_id = ?',
                [$member['total_seconds'], (int) $periodId, $member['user_id']]);
        }
        service_query('UPDATE payroll_periods SET ended_by = ?, ended_at = ? WHERE id = ?',
            [(int) $actorId, $endedAt, (int) $periodId]);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function record_payout($actorId, $periodId, $targetId, $expectedPaidSeconds, $expectedTalerCents)
{
    db()->beginTransaction();
    try {
        $ids = array_unique([(int) $actorId, (int) $targetId]);
        sort($ids);
        $locked = [];
        foreach ($ids as $id) {
            $locked[$id] = service_query('SELECT * FROM users WHERE id = ? FOR UPDATE', [$id])->fetch();
        }
        $actor = $locked[$actorId];
        $user = $locked[$targetId];
        if (!$actor || !can_view_payroll($actor)) {
            throw new DomainException('Die Lohnverwaltung ist erst ab Rang 65 erlaubt.');
        }
        if (!$user || (int) $user['rank_id'] < 1 || (int) $user['rank_id'] > 49) {
            throw new DomainException('Lohn wird ausschließlich für Mitarbeiter der Ränge 1 bis 49 berechnet.');
        }
        $period = service_query('SELECT * FROM payroll_periods WHERE id = ? FOR UPDATE', [(int) $periodId])->fetch();
        $member = service_query('SELECT * FROM payroll_period_members WHERE period_id = ? AND user_id = ? FOR UPDATE',
            [(int) $periodId, (int) $targetId])->fetch();
        if (!$period || !$member || (int) $period['rank_id'] < 1 || (int) $period['rank_id'] > 49) {
            throw new DomainException('Mitarbeiter ist keiner passenden Lohnperiode zugeordnet.');
        }
        $now = time();
        accrue_time($user, $now);
        create_due_promotion($user, $now);
        $summary = payroll_period_summary($periodId, $targetId)['members'][0] ?? null;
        if (!$summary || $summary['payable_hours'] < 1) {
            throw new DomainException('Keine volle, noch unbezahlte Dienststunde in dieser Periode vorhanden.');
        }
        if ((int) $member['paid_seconds'] !== (int) $expectedPaidSeconds
            || (int) $summary['payable_cents'] !== (int) $expectedTalerCents) {
            throw new DomainException('Der Lohnstand hat sich geändert. Bitte neu laden und den Betrag erneut prüfen.');
        }
        $seconds = (int) $summary['payable_hours'] * PROMOTION_INTERVAL;
        $amount = number_format((int) $summary['payable_cents'] / 100, 2, '.', '');
        service_query('INSERT INTO payouts (period_id, user_id, actor_id, paid_seconds, taler, paid_at) VALUES (?, ?, ?, ?, ?, ?)',
            [(int) $periodId, (int) $targetId, (int) $actorId, $seconds, $amount, $now]);
        service_query('UPDATE payroll_period_members SET paid_seconds = paid_seconds + ? WHERE period_id = ? AND user_id = ?',
            [$seconds, (int) $periodId, (int) $targetId]);
        service_query('UPDATE users SET paid_seconds = paid_seconds + ? WHERE id = ?', [$seconds, (int) $targetId]);
        db()->commit();
        return (int) $summary['payable_cents'];
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}
