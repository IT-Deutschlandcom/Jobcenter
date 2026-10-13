<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
if (!can_manage($user)) {
    http_response_code(403);
    exit('Zugriff erst ab Rang 50 Squad Leader.');
}
synchronize_visible_users($user);
$search = trim(isset($_GET['q']) && is_string($_GET['q']) ? $_GET['q'] : '');
$members = service_query(
    'SELECT id, username, rank_id, rank_step, active_since FROM users WHERE rank_id BETWEEN 1 AND 49'
        . ($search === '' ? '' : " AND username LIKE ? ESCAPE '!'") . ' ORDER BY username',
    $search === '' ? [] : ['%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%']
)->fetchAll();
$pending = pending_promotions($user);
$history = service_query('SELECT p.*, u.username, c.username AS creator_name, f.username AS confirmer_name FROM promotions p JOIN users u ON u.id = p.user_id JOIN users c ON c.id = p.created_by LEFT JOIN users f ON f.id = p.confirmed_by ORDER BY p.id DESC LIMIT 100')->fetchAll();
$duty = service_query('SELECT d.*, u.username, a.username AS actor_name FROM duty_log d JOIN users u ON u.id = d.user_id JOIN users a ON a.id = d.actor_id ORDER BY d.id DESC LIMIT 100')->fetchAll();
$adjustments = service_query('SELECT x.*, u.username, a.username AS actor_name FROM payroll_adjustments x JOIN users u ON u.id = x.user_id JOIN users a ON a.id = x.actor_id ORDER BY x.id DESC LIMIT 100')->fetchAll();
page_header('BF-Panel', $user);
?>
<section class="card">
    <h2>Fällige Beförderungen <span class="badge"><?= count($pending) ?></span></h2>
    <p>Pro voller Dienststunde eine Bestätigung. Keine Selbstbeförderungen; keine automatische Vergabe von Führungsrechten.</p>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Von → Nach</th><th>Erstellt für Dienst von</th><th>Aktion</th></tr></thead><tbody>
    <?php foreach ($pending as $promotion): ?>
        <tr><td><?= h($promotion['username']) ?></td><td><?= h(rank_label(['rank_id' => $promotion['from_rank'], 'rank_step' => $promotion['from_step']])) ?> → <?= h(rank_label(['rank_id' => $promotion['to_rank'], 'rank_step' => $promotion['to_step']])) ?></td><td><?= h($promotion['creator_name']) ?></td><td><?php if ($promotion['user_id'] != $user['id']) { action_form($promotion['user_id'], 'confirm', 'Bestätigen', $promotion['id']); } else { echo 'Andere Führungskraft erforderlich'; } ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$pending): ?><tr><td colspan="4">Keine Beförderungen offen.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Dienstverwaltung</h2>
    <p>Nur Ränge 1 bis 49 können angemeldet werden. Dienst ist täglich von 08:00 bis 01:00 Uhr möglich; um 01:00 Uhr erfolgt die automatische Abmeldung. Bonusminuten bitte positiv, Strafminuten negativ eintragen. Führungskräfte werden nicht für die Lohnberechnung angemeldet.</p>
    <form method="get" action="bf.php">
        <label for="member-search">Mitarbeiter suchen</label>
        <input id="member-search" type="search" name="q" value="<?= h($search) ?>" placeholder="Benutzername">
        <button type="submit">Suchen</button>
        <?php if ($search !== ''): ?><a class="button secondary" href="bf.php">Suche zurücksetzen</a><?php endif; ?>
    </form>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Rang</th><th>Status</th><th>Dienst</th></tr></thead><tbody>
    <?php foreach ($members as $member): ?>
        <tr><td><?= h($member['username']) ?></td><td><?= h(rank_label($member)) ?></td><td><span class="badge"><?= $member['active_since'] === null ? 'Abgemeldet' : 'Angemeldet' ?></span></td><td><?php if ($member['id'] == $user['id'] && $member['active_since'] === null) { echo 'Andere Führungskraft erforderlich'; } else { action_form($member['id'], $member['active_since'] === null ? 'start' : 'stop', $member['active_since'] === null ? 'Anmelden' : 'Abmelden'); } ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Beförderungsprotokoll <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Neuer Rang</th><th>Erstellt für Dienst von / am</th><th>Bestätigt von / am</th></tr></thead><tbody>
    <?php foreach ($history as $promotion): ?>
        <tr><td><?= h($promotion['username']) ?></td><td><?= h(rank_label(['rank_id' => $promotion['to_rank'], 'rank_step' => $promotion['to_step']])) ?></td><td><?= h($promotion['creator_name']) ?><br><?= h(date('d.m.Y H:i', $promotion['created_at'])) ?></td><td><?= h($promotion['confirmer_name'] ?? 'Offen') ?><?= $promotion['confirmed_at'] ? '<br>' . h(date('d.m.Y H:i', $promotion['confirmed_at'])) : '' ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Dienstprotokoll <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Aktion</th><th>Durchgeführt von</th><th>Zeitpunkt</th></tr></thead><tbody>
    <?php foreach ($duty as $entry): ?>
        <tr><td><?= h($entry['username']) ?></td><td><?= $entry['action'] === 'start' ? 'Angemeldet' : ($entry['action'] === 'auto_stop' ? 'Automatisch abgemeldet' : 'Abgemeldet') ?></td><td><?= h($entry['actor_name']) ?></td><td><?= h(date('d.m.Y H:i', $entry['happened_at'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Bonus- und Strafminuten <small>Letzte 100 Einträge</small></h2>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Minuten</th><th>Grund</th><th>Gesetzt von</th><th>Zeitpunkt</th></tr></thead><tbody>
    <?php foreach ($adjustments as $adjustment): ?>
        <tr><td><?= h($adjustment['username']) ?></td><td><?= (int) ($adjustment['adjustment_seconds'] / 60) ?></td><td><?= (int) $adjustment['adjustment_seconds'] > 0 ? 'Bonus' : 'Strafe' ?></td><td><?= h($adjustment['actor_name']) ?></td><td><?= h(date('d.m.Y H:i', $adjustment['created_at'])) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$adjustments): ?><tr><td colspan="5">Noch keine Zeitkorrekturen.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
