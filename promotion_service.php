<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/rank_catalog.php';

const PROMOTION_INTERVAL = 3600;

function service_query($sql, $params = [])
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}

function earned_seconds($user, $now)
{
    if ($user['active_since'] === null) {
        return 0;
    }
    $start = (int) $user['active_since'];
    $hour = (int) date('G', $start);
    if ($hour >= 1 && $hour < 8) {
        return 0;
    }
    $windowEnd = $hour < 1
        ? strtotime('today 01:00', $start)
        : strtotime('tomorrow 01:00', $start);
    return max(0, min((int) $now, $windowEnd) - $start);
}

function duty_open($timestamp)
{
    $hour = (int) date('G', $timestamp);
    return $hour >= 8 || $hour < 1;
}

function accrue_time(&$user, $now)
{
    $elapsed = earned_seconds($user, $now);
    if ($user['active_since'] !== null) {
        $start = (int) $user['active_since'];
        $hour = (int) date('G', $start);
        $windowEnd = $hour >= 1 && $hour < 8
            ? $start
            : ($hour < 1 ? strtotime('today 01:00', $start) : strtotime('tomorrow 01:00', $start));
        $closed = $now >= $windowEnd;
        service_query('UPDATE users SET total_seconds = total_seconds + ?, promotion_seconds = promotion_seconds + ?, active_since = ?, active_by = ? WHERE id = ?',
            [$elapsed, $elapsed, $closed ? null : $now, $closed ? null : $user['active_by'], $user['id']]);
        $user['total_seconds'] += $elapsed;
        $user['promotion_seconds'] += $elapsed;
        $user['active_since'] = $closed ? null : $now;
        if ($closed) {
            service_query('INSERT INTO duty_log (user_id, actor_id, action, happened_at) VALUES (?, ?, ?, ?)',
                [$user['id'], $user['active_by'] ?: $user['id'], 'auto_stop', $windowEnd]);
        }
    }
}

function create_due_promotion($user, $now)
{
    $next = next_rank($user);
    if (!$next || (int) $user['promotion_seconds'] < PROMOTION_INTERVAL) {
        return;
    }
    $pending = service_query('SELECT id FROM promotions WHERE user_id = ? AND confirmed_at IS NULL', [$user['id']])->fetch();
    if (!$pending) {
        service_query('INSERT INTO promotions (user_id, from_rank, from_step, to_rank, to_step, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$user['id'], $user['rank_id'], $user['rank_step'], $next['rank_id'], $next['rank_step'],
             $user['active_by'] ?: $user['id'], $now]);
    }
}

function synchronize_user($id)
{
    db()->beginTransaction();
    try {
        $user = service_query('SELECT * FROM users WHERE id = ? FOR UPDATE', [$id])->fetch();
        if ($user) {
            $now = time();
            accrue_time($user, $now);
            create_due_promotion($user, $now);
        }
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function synchronize_visible_users($viewer)
{
    if (can_manage($viewer)) {
        $ids = service_query('SELECT id FROM users WHERE active_since IS NOT NULL OR promotion_seconds >= ?', [PROMOTION_INTERVAL])->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $ids = [$viewer['id']];
    }
    foreach ($ids as $id) {
        synchronize_user($id);
    }
}

function duty_action($actorId, $targetId, $action, $promotionId = 0, $adjustmentMinutes = 0, $now = null)
{
    $now = $now ?? time();
    if ($action === 'start' && !duty_open($now)) {
        throw new DomainException('Dienstanmeldungen sind täglich nur von 08:00 bis 01:00 Uhr möglich.');
    }
    db()->beginTransaction();
    try {
        // Einheitliche Sperrreihenfolge verhindert konkurrierende Doppelbeförderungen.
        $ids = array_unique([(int) $actorId, (int) $targetId]);
        sort($ids);
        $locked = [];
        foreach ($ids as $id) {
            $locked[$id] = service_query('SELECT * FROM users WHERE id = ? FOR UPDATE', [$id])->fetch();
        }
        $actor = $locked[$actorId];
        $user = $locked[$targetId];
        if (!$actor || !$user) {
            throw new DomainException('Benutzer nicht gefunden.');
        }
        if (!can_manage($actor) && !($action === 'stop' && $actorId == $targetId)) {
            throw new DomainException('Keine Berechtigung für diese Aktion.');
        }
        if ((int) $user['rank_id'] < 1 || (int) $user['rank_id'] > 49) {
            throw new DomainException('Dienstverwaltung ist ausschließlich für Ränge 1 bis 49 vorgesehen.');
        }
        if ($action === 'start' && $actorId == $targetId) {
            throw new DomainException('Selbsteintragungen sind nicht erlaubt. Bitte eine andere Führungskraft um Dienstanmeldung.');
        }
        $wasActive = $user['active_since'] !== null;
        accrue_time($user, $now);
        if ($action === 'stop' && $wasActive && $user['active_since'] === null) {
            create_due_promotion($user, $now);
            db()->commit();
            return;
        }
        if ($action === 'start') {
            if (!duty_open($now)) {
                throw new DomainException('Dienstanmeldungen sind täglich nur von 08:00 bis 01:00 Uhr möglich.');
            }
            if ($user['active_since'] !== null) {
                throw new DomainException('Dieser Mitarbeiter ist bereits im Dienst.');
            }
            service_query('UPDATE users SET active_since = ?, active_by = ? WHERE id = ?', [$now, $actorId, $targetId]);
            $user['active_since'] = $now;
            $user['active_by'] = $actorId;
        } elseif ($action === 'stop') {
            if ($user['active_since'] === null) {
                throw new DomainException('Dieser Mitarbeiter ist bereits abgemeldet.');
            }
            service_query('UPDATE users SET active_since = NULL, active_by = NULL WHERE id = ?', [$targetId]);
            $user['active_since'] = null;
        } elseif ($action === 'confirm') {
            if ($actorId == $targetId) {
                throw new DomainException('Eigene Beförderungen können nicht bestätigt werden.');
            }
            $promotion = service_query('SELECT * FROM promotions WHERE id = ? AND user_id = ? AND confirmed_at IS NULL FOR UPDATE',
                [$promotionId, $targetId])->fetch();
            $next = next_rank($user);
            if (!$promotion || !$next || (int) $user['promotion_seconds'] < PROMOTION_INTERVAL ||
                (int) $promotion['from_rank'] !== (int) $user['rank_id'] ||
                (int) $promotion['from_step'] !== (int) $user['rank_step'] ||
                (int) $promotion['to_rank'] !== $next['rank_id'] ||
                (int) $promotion['to_step'] !== $next['rank_step']) {
                throw new DomainException('Die Beförderung ist nicht mehr gültig.');
            }
            if ($next['rank_id'] !== (int) $user['rank_id'] && (int) $user['rank_id'] <= 49) {
                $period = service_query('SELECT p.id FROM payroll_periods p JOIN payroll_period_members m ON m.period_id = p.id WHERE p.rank_id = ? AND p.ended_at IS NULL AND m.user_id = ?',
                    [$user['rank_id'], $targetId])->fetch();
                if ($period) {
                    service_query('UPDATE payroll_period_members SET final_total_seconds = ? WHERE period_id = ? AND user_id = ? AND final_total_seconds IS NULL',
                        [$user['total_seconds'], $period['id'], $targetId]);
                }
            }
            service_query('UPDATE users SET rank_id = ?, rank_step = ?, promotion_seconds = promotion_seconds - ? WHERE id = ?',
                [$next['rank_id'], $next['rank_step'], PROMOTION_INTERVAL, $targetId]);
            if ($next['rank_id'] <= 49) {
                $period = service_query('SELECT id FROM payroll_periods WHERE rank_id = ? AND ended_at IS NULL',
                    [$next['rank_id']])->fetch();
                if ($period) {
                    service_query('INSERT IGNORE INTO payroll_period_members (period_id, user_id, baseline_seconds) VALUES (?, ?, ?)',
                        [$period['id'], $targetId, $user['total_seconds']]);
                }
            }
            service_query('UPDATE promotions SET confirmed_by = ?, confirmed_at = ? WHERE id = ?', [$actorId, $now, $promotionId]);
            $user = array_merge($user, $next);
            $user['promotion_seconds'] -= PROMOTION_INTERVAL;
        } else {
            throw new DomainException('Unbekannte Aktion.');
        }
        $adjustmentMinutes = (int) $adjustmentMinutes;
        if ($adjustmentMinutes !== 0) {
            if (!can_manage($actor) || !in_array($action, ['start', 'stop'], true) || abs($adjustmentMinutes) > 1440) {
                throw new DomainException('Bonus- und Strafminuten dürfen nur Führungskräfte bis maximal 1440 Minuten setzen.');
            }
            $period = service_query('SELECT p.id FROM payroll_periods p JOIN payroll_period_members m ON m.period_id = p.id WHERE p.rank_id = ? AND p.ended_at IS NULL AND m.user_id = ?',
                [$user['rank_id'], $targetId])->fetch();
            if (!$period) {
                throw new DomainException('Für den Rang des Mitarbeiters muss zuerst eine Lohnperiode gestartet werden.');
            }
            $adjustmentSeconds = $adjustmentMinutes * 60;
            service_query('INSERT INTO payroll_adjustments (period_id, user_id, actor_id, duty_action, adjustment_seconds, created_at) VALUES (?, ?, ?, ?, ?, ?)',
                [$period['id'], $targetId, $actorId, $action, $adjustmentSeconds, $now]);
            service_query('UPDATE payroll_period_members SET adjustment_seconds = adjustment_seconds + ? WHERE period_id = ? AND user_id = ?',
                [$adjustmentSeconds, $period['id'], $targetId]);
        }
        if ($action !== 'confirm') {
            service_query('INSERT INTO duty_log (user_id, actor_id, action, happened_at) VALUES (?, ?, ?, ?)',
                [$targetId, $actorId, $action, $now]);
        }
        create_due_promotion($user, $now);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}

function pending_promotions($viewer)
{
    $sql = 'SELECT p.*, u.username, creator.username AS creator_name FROM promotions p JOIN users u ON u.id = p.user_id JOIN users creator ON creator.id = p.created_by WHERE p.confirmed_at IS NULL';
    return service_query($sql . (can_manage($viewer) ? '' : ' AND p.user_id = ?') . ' ORDER BY p.created_at, p.id',
        can_manage($viewer) ? [] : [$viewer['id']])->fetchAll();
}
