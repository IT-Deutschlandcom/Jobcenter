<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
synchronize_visible_users($user);
$user = current_user();
$work = work_summary($user, time());
$period = (int) $user['rank_id'] >= 1 && (int) $user['rank_id'] <= 49
    ? active_payroll_period((int) $user['rank_id']) : null;
$periodMember = $period ? (payroll_period_summary($period['id'], $user['id'])['members'][0] ?? null) : null;
$members = service_query('SELECT id, username, rank_id, rank_step, total_seconds, promotion_seconds, active_since FROM users'
    . (can_manage($user) ? '' : ' WHERE id = ?') . ' ORDER BY total_seconds DESC, username',
    can_manage($user) ? [] : [$user['id']])->fetchAll();
$payouts = service_query('SELECT p.*, a.username AS actor_name FROM payouts p JOIN users a ON a.id = p.actor_id WHERE p.user_id = ? ORDER BY p.id DESC LIMIT 100', [$user['id']])->fetchAll();
page_header('Gesammelte Stunden', $user);
?>
<section class="card">
    <h2>Deine Stunden<?= (int) $user['rank_id'] <= 49 ? ' & dein Lohn' : '' ?></h2>
    <p>Gesamtzeit: <strong id="work-total"><?= h(duration_label($work['total_seconds'])) ?></strong></p>
    <?php if ((int) $user['rank_id'] >= 1 && (int) $user['rank_id'] <= 49): ?>
        <?php if ($periodMember): ?>
            <p>Lohnperiode: <?= h(duration_label($periodMember['unpaid_seconds'])) ?> offen · <?= h(format_taler($periodMember['payable_cents'])) ?> auszahlbar · <?= h(format_taler($periodMember['paid_cents'])) ?> in dieser Periode ausgezahlt.</p>
        <?php else: ?>
            <p>Für deinen Rang läuft derzeit keine Lohnperiode. Die Führung kann eine Periode starten.</p>
        <?php endif; ?>
        <p>Grundlohn 2,50 Taler pro voller Stunde. Restminuten bleiben innerhalb der jeweiligen Lohnperiode erhalten.</p>
    <?php else: ?>
        <p>Vorgesetzte sind von der Mitarbeiter-Lohnberechnung ausgeschlossen. Ihre BF-Stunden werden separat sonntags addiert.</p>
    <?php endif; ?>
</section>
<section class="card">
    <h2><?= can_manage($user) ? 'Dienstzeit-Rangliste' : 'Deine Dienstzeit' ?></h2>
    <p>Gesamte angemeldete Zeit über alle Dienste. Beförderungen verbrauchen nur das Zeitguthaben, niemals die Gesamtstunden.</p>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Rang</th><th>Gesammelte Zeit</th><th>Beförderungsguthaben</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($members as $member): ?>
        <tr><td><?= h($member['username']) ?></td><td><?= h(rank_label($member)) ?></td><td><?= (int) floor($member['total_seconds'] / 3600) ?> h <?= (int) floor($member['total_seconds'] % 3600 / 60) ?> min</td><td><?= (int) floor($member['promotion_seconds'] / 3600) ?> h <?= (int) floor($member['promotion_seconds'] % 3600 / 60) ?> min</td><td><?= $member['active_since'] === null ? 'Abgemeldet' : 'Im Dienst' ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Deine Auszahlungen <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Zeitpunkt</th><th>Bezahlte Stunden</th><th>Taler</th><th>Bestätigt von</th></tr></thead><tbody>
    <?php foreach ($payouts as $payout): ?>
        <tr><td><?= h(date('d.m.Y H:i', $payout['paid_at'])) ?></td><td><?= h(duration_label($payout['paid_seconds'])) ?></td><td><?= (int) $payout['taler'] ?></td><td><?= h($payout['actor_name']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$payouts): ?><tr><td colspan="4">Noch keine Auszahlung bestätigt.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
