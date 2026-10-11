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
    return $user['active_since'] === null ? 0 : max(0, $now - (int) $user['active_since']);
}

function accrue_time(&$user, $now)
{
    $elapsed = earned_seconds($user, $now);
    if ($user['active_since'] !== null) {
        service_query('UPDATE users SET total_seconds = total_seconds + ?, promotion_seconds = promotion_seconds + ?, active_since = ? WHERE id = ?',
            [$elapsed, $elapsed, $now, $user['id']]);
        $user['total_seconds'] += $elapsed;
        $user['promotion_seconds'] += $elapsed;
        $user['active_since'] = $now;
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

function duty_action($actorId, $targetId, $action, $promotionId = 0)
{
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
        if ($action === 'start' && $actorId == $targetId) {
            throw new DomainException('Selbsteintragungen sind nicht erlaubt. Bitte eine andere Führungskraft um Dienstanmeldung.');
        }
        $now = time();
        accrue_time($user, $now);
        if ($action === 'start') {
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
            service_query('UPDATE users SET active_since = NULL WHERE id = ?', [$targetId]);
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
            service_query('UPDATE users SET rank_id = ?, rank_step = ?, promotion_seconds = promotion_seconds - ? WHERE id = ?',
                [$next['rank_id'], $next['rank_step'], PROMOTION_INTERVAL, $targetId]);
            service_query('UPDATE promotions SET confirmed_by = ?, confirmed_at = ? WHERE id = ?', [$actorId, $now, $promotionId]);
            $user = array_merge($user, $next);
            $user['promotion_seconds'] -= PROMOTION_INTERVAL;
        } else {
            throw new DomainException('Unbekannte Aktion.');
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
