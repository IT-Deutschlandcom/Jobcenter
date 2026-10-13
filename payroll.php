<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
if (!can_view_payroll($user)) {
    http_response_code(403);
    exit('Die Lohnberechnung ist erst ab Rang 65 sichtbar.');
}
synchronize_visible_users($user);
$periodRows = service_query('SELECT id, rank_id, started_at, ended_at FROM payroll_periods ORDER BY id DESC LIMIT 100')->fetchAll();
$periods = [];
$totalCents = 0;
$activeWorkers = 0;
foreach ($periodRows as $periodRow) {
    $summary = payroll_period_summary($periodRow['id']);
    if (!$summary) {
        continue;
    }
    foreach ($summary['members'] as $member) {
        $totalCents += $member['payable_cents'];
        $activeWorkers += $member['active_since'] !== null ? 1 : 0;
    }
    $periods[] = $summary;
}
$history = service_query('SELECT p.*, u.username, a.username AS actor_name FROM payouts p JOIN users u ON u.id = p.user_id JOIN users a ON a.id = p.actor_id ORDER BY p.id DESC LIMIT 100')->fetchAll();
page_header('Lohnberechnung', $user);
?>
<div class="stats">
    <section class="card"><span>Offener Gesamtlohn</span><strong><?= h(format_taler($totalCents)) ?></strong></section>
    <section class="card"><span>Mitarbeiter im Dienst</span><strong><?= $activeWorkers ?></strong></section>
    <section class="card"><span>Aktive Lohnperioden</span><strong><?= count(array_filter($periods, fn($period) => $period['period']['ended_at'] === null)) ?></strong><a href="payroll_periods.php">Lohnperioden verwalten</a></section>
</div>
<section class="card">
    <h2>Offene Lohnbeträge nach Periode</h2>
    <p>Ränge 1 bis 49 erhalten 2,50 Taler je voller unbezahlter Stunde. Laufende Perioden können jederzeit berechnet werden; Restminuten bleiben innerhalb dieser Periode erhalten. Rang 50 und höher ist von dieser Mitarbeiter-Lohnberechnung ausgeschlossen.</p>
    <?php foreach ($periods as $summary): ?>
        <?php $period = $summary['period']; ?>
        <h3>Rang <?= (int) $period['rank_id'] ?> · <?= h(rank_catalog()[(int) $period['rank_id']]['name'] ?? 'Unbekannter Rang') ?>
            · <?= $period['ended_at'] === null ? 'Läuft' : 'Beendet' ?>
            <small><?= h(date('d.m.Y H:i', $period['started_at'])) ?><?= $period['ended_at'] === null ? '' : ' – ' . h(date('d.m.Y H:i', $period['ended_at'])) ?></small>
        </h3>
        <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Periode geleistet</th><th>Bonus/Strafe</th><th>Unbezahlt</th><th>Offener Lohn</th><th>Aktion</th></tr></thead><tbody>
        <?php foreach ($summary['members'] as $member): ?>
            <tr>
                <td><?= h($member['username']) ?><br><small><?= h(rank_label($member)) ?></small></td>
                <td><?= h(duration_label($member['period_seconds'])) ?></td>
                <td><?= h(($member['adjustment_seconds'] >= 0 ? '+' : '−') . duration_label(abs((int) $member['adjustment_seconds']))) ?></td>
                <td><?= h(duration_label($member['unpaid_seconds'])) ?></td>
                <td><?= h(format_taler($member['payable_cents'])) ?></td>
                <td>
                    <?php if ($member['payable_hours'] > 0): ?>
                        <form method="post" action="payroll_action.php">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="period_id" value="<?= (int) $period['id'] ?>">
                            <input type="hidden" name="user_id" value="<?= (int) $member['user_id'] ?>">
                            <input type="hidden" name="paid_seconds" value="<?= (int) $member['paid_seconds'] ?>">
                            <input type="hidden" name="taler_cents" value="<?= (int) $member['payable_cents'] ?>">
                            <label><input type="checkbox" name="delivered" value="1" required> Taler tatsächlich übergeben</label>
                            <button>Auszahlung bestätigen</button>
                        </form>
                    <?php else: ?>Keine volle Stunde offen<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$summary['members']): ?><tr><td colspan="6">Zum Startzeitpunkt waren diesem Rang keine Mitarbeiter zugeordnet.</td></tr><?php endif; ?>
        </tbody></table></div>
    <?php endforeach; ?>
    <?php if (!$periods): ?><p>Es gibt noch keine Lohnperioden. Starte zuerst für einen Rang eine Periode.</p><?php endif; ?>
</section>
<section class="card">
    <h2>Auszahlungsprotokoll <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Stunden</th><th>Taler</th><th>Bestätigt von</th><th>Zeitpunkt</th></tr></thead><tbody>
    <?php foreach ($history as $payout): ?>
        <tr><td><?= h($payout['username']) ?></td><td><?= h(duration_label($payout['paid_seconds'])) ?></td><td><?= h(number_format((float) $payout['taler'], 2, ',', '.')) ?></td><td><?= h($payout['actor_name']) ?></td><td><?= h(date('d.m.Y H:i', $payout['paid_at'])) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$history): ?><tr><td colspan="5">Noch keine Auszahlungen bestätigt.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
