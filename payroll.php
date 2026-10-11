<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
if (!can_view_payroll($user)) {
    http_response_code(403);
    exit('Die Lohnverwaltung ist nur für Director-Ränge sichtbar.');
}
synchronize_visible_users($user);
$members = service_query('SELECT id, username, rank_id, rank_step, total_seconds, paid_seconds, active_since FROM users ORDER BY username')->fetchAll();
$now = time();
$totalTaler = 0;
$active = 0;
foreach ($members as &$member) {
    $member['work'] = work_summary($member, $now);
    $totalTaler += $member['work']['payable_taler'];
    $active += $member['active_since'] !== null ? 1 : 0;
}
unset($member);
$pendingCount = count(pending_promotions($user));
$history = service_query('SELECT p.*, u.username, a.username AS actor_name FROM payouts p JOIN users u ON u.id = p.user_id JOIN users a ON a.id = p.actor_id ORDER BY p.id DESC LIMIT 100')->fetchAll();
page_header('Director · Lohnverwaltung', $user);
?>
<div class="stats">
    <section class="card"><span>Offener Gesamtlohn</span><strong><?= $totalTaler ?> Taler</strong></section>
    <section class="card"><span>Mitarbeiter im Dienst</span><strong><?= $active ?> / <?= count($members) ?></strong></section>
    <section class="card"><span>Offene Beförderungen</span><strong><?= $pendingCount ?></strong><a href="bf.php">Im BF-Panel prüfen</a></section>
</div>
<section class="card">
    <h2>Lohnstände aller Mitarbeiter</h2>
    <p>4 Taler je voller unbezahlter Stunde. Erst nach tatsächlicher Übergabe der Taler die Auszahlung bestätigen. Es erfolgt keine automatische Habbo-Überweisung.</p>
    <p>Bestätigt werden alle derzeit vollen unbezahlten Stunden. Restminuten und Beförderungsguthaben bleiben erhalten.</p>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter / Rang</th><th>Gesamtzeit</th><th>Unbezahlte Zeit</th><th>Offener Lohn</th><th>Bereits bezahlt</th><th>Aktion</th></tr></thead><tbody>
    <?php foreach ($members as $member): ?>
        <tr><td><?= h($member['username']) ?><br><small><?= h(rank_label($member)) ?></small></td><td><?= h(duration_label($member['work']['total_seconds'])) ?></td><td><?= h(duration_label($member['work']['unpaid_seconds'])) ?></td><td><?= $member['work']['payable_taler'] ?> Taler</td><td><?= $member['work']['paid_taler'] ?> Taler</td><td>
        <?php if ($member['work']['payable_hours'] > 0): ?>
            <form method="post" action="payroll_action.php">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">
                <input type="hidden" name="paid_seconds" value="<?= (int) $member['paid_seconds'] ?>">
                <input type="hidden" name="taler" value="<?= $member['work']['payable_taler'] ?>">
                <label><input type="checkbox" name="delivered" value="1" required> Taler tatsächlich übergeben</label>
                <button>Auszahlung bestätigen</button>
            </form>
        <?php else: ?>Keine volle Stunde offen<?php endif; ?>
        </td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Auszahlungsprotokoll <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Stunden</th><th>Taler</th><th>Bestätigt von</th><th>Zeitpunkt</th></tr></thead><tbody>
    <?php foreach ($history as $payout): ?>
        <tr><td><?= h($payout['username']) ?></td><td><?= h(duration_label($payout['paid_seconds'])) ?></td><td><?= (int) $payout['taler'] ?></td><td><?= h($payout['actor_name']) ?></td><td><?= h(date('d.m.Y H:i', $payout['paid_at'])) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$history): ?><tr><td colspan="5">Noch keine Auszahlungen bestätigt.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
