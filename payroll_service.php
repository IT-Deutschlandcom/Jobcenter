<?php
require_once __DIR__ . '/promotion_service.php';

const TALER_PER_HOUR = 4;

function work_summary($user, $now)
{
    $total = (int) $user['total_seconds'] + earned_seconds($user, $now);
    $unpaid = max(0, $total - (int) $user['paid_seconds']);
    return [
        'total_seconds' => $total,
        'unpaid_seconds' => $unpaid,
        'payable_hours' => intdiv($unpaid, PROMOTION_INTERVAL),
        'payable_taler' => intdiv($unpaid, PROMOTION_INTERVAL) * TALER_PER_HOUR,
        'paid_taler' => intdiv((int) $user['paid_seconds'], PROMOTION_INTERVAL) * TALER_PER_HOUR,
    ];
}

function duration_label($seconds)
{
    return intdiv((int) $seconds, 3600) . ' h ' . intdiv((int) $seconds % 3600, 60) . ' min';
}

function record_payout($actorId, $targetId, $expectedPaidSeconds, $expectedTaler)
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
            throw new DomainException('Lohnverwaltung ist nur für Director-Ränge erlaubt.');
        }
        if (!$user) {
            throw new DomainException('Benutzer nicht gefunden.');
        }
        $now = time();
        accrue_time($user, $now);
        create_due_promotion($user, $now);
        $summary = work_summary($user, $now);
        if ($summary['payable_hours'] < 1) {
            throw new DomainException('Keine volle, noch unbezahlte Dienststunde vorhanden.');
        }
        if ((int) $user['paid_seconds'] !== $expectedPaidSeconds || $summary['payable_taler'] !== $expectedTaler) {
            throw new DomainException('Der Lohnstand hat sich geändert. Bitte neu laden und den Betrag erneut prüfen.');
        }
        $seconds = $summary['payable_hours'] * PROMOTION_INTERVAL;
        service_query('INSERT INTO payouts (user_id, actor_id, paid_seconds, taler, paid_at) VALUES (?, ?, ?, ?, ?)',
            [$targetId, $actorId, $seconds, $summary['payable_taler'], $now]);
        service_query('UPDATE users SET paid_seconds = paid_seconds + ? WHERE id = ?', [$seconds, $targetId]);
        db()->commit();
        return $summary['payable_taler'];
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}
