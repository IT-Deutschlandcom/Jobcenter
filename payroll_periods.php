<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
if (!can_manage_payroll_periods($user)) {
    http_response_code(403);
    exit('Lohnperioden dürfen erst ab Rang 65 verwaltet werden.');
}
$periods = service_query(
    'SELECT p.*, s.username AS starter_name, e.username AS ender_name
     FROM payroll_periods p JOIN users s ON s.id = p.started_by
     LEFT JOIN users e ON e.id = p.ended_by
     ORDER BY p.id DESC LIMIT 100'
)->fetchAll();
$activeByRank = [];
foreach (service_query('SELECT id, rank_id, started_at FROM payroll_periods WHERE ended_at IS NULL')->fetchAll() as $period) {
    $activeByRank[(int) $period['rank_id']] = $period;
}
page_header('Lohnperioden', $user);
?>
<section class="card">
    <h2>Perioden je Rang verwalten</h2>
    <p>Eine Periode kann bereits im laufenden Betrieb berechnet werden. Beim Start beginnt die Erfassung ab dem Startzeitpunkt; bereits geleistete Stunden davor werden nicht rückwirkend einbezogen. Auszahlungen können während einer laufenden oder nach dem Beenden einer Periode ausgelöst werden.</p>
    <p>Nur Mitarbeitende der Ränge 1 bis 49 werden in diese Lohnperioden aufgenommen. Führungskräfte erhalten keine Mitarbeiter-Lohnabrechnung. Ihre BF-Stunden werden separat sonntags addiert; die erforderliche Stundenschwelle muss durch die Führung festgelegt werden.</p>
    <div class="table-scroll"><table><thead><tr><th>Rang</th><th>Grundlohn</th><th>Status</th><th>Aktion</th></tr></thead><tbody>
    <?php foreach (range(1, 49) as $rankId): ?>
        <?php $rank = rank_catalog()[$rankId]; $period = $activeByRank[$rankId] ?? null; ?>
        <tr>
            <td><?= $rankId ?> · <?= h($rank['name']) ?></td>
            <td><?= h(format_taler(payroll_rate_cents($rankId))) ?> je Stunde</td>
            <td><?= $period ? 'Läuft seit ' . h(date('d.m.Y H:i', $period['started_at'])) : 'Keine aktive Periode' ?></td>
            <td>
                <?php if ($period): ?>
                    <form class="inline-form" method="post" action="payroll_period_action.php">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="end">
                        <input type="hidden" name="period_id" value="<?= (int) $period['id'] ?>">
                        <button class="secondary">Periode beenden</button>
                    </form>
                <?php else: ?>
                    <form class="inline-form" method="post" action="payroll_period_action.php">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="start">
                        <input type="hidden" name="rank_id" value="<?= $rankId ?>">
                        <button>Periode starten</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Periodenprotokoll <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Rang</th><th>Begonnen von / am</th><th>Beendet von / am</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($periods as $period): ?>
        <tr>
            <td><?= (int) $period['rank_id'] ?> · <?= h(rank_catalog()[(int) $period['rank_id']]['name'] ?? 'Unbekannter Rang') ?></td>
            <td><?= h($period['starter_name']) ?> · <?= h(date('d.m.Y H:i', $period['started_at'])) ?></td>
            <td><?= $period['ended_at'] === null ? '—' : h($period['ender_name'] . ' · ' . date('d.m.Y H:i', $period['ended_at'])) ?></td>
            <td><?= $period['ended_at'] === null ? 'Läuft' : 'Beendet' ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$periods): ?><tr><td colspan="4">Noch keine Lohnperioden gestartet.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
