<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
if (!can_manage($user)) {
    http_response_code(403);
    exit('Zugriff erst ab Rang 50 Squad Leader.');
}
synchronize_visible_users($user);
$members = service_query('SELECT id, username, rank_id, rank_step, active_since FROM users ORDER BY username')->fetchAll();
$pending = pending_promotions($user);
$history = service_query('SELECT p.*, u.username, c.username AS creator_name, f.username AS confirmer_name FROM promotions p JOIN users u ON u.id = p.user_id JOIN users c ON c.id = p.created_by LEFT JOIN users f ON f.id = p.confirmed_by ORDER BY p.id DESC LIMIT 100')->fetchAll();
$duty = service_query('SELECT d.*, u.username, a.username AS actor_name FROM duty_log d JOIN users u ON u.id = d.user_id JOIN users a ON a.id = d.actor_id ORDER BY d.id DESC LIMIT 100')->fetchAll();
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
        <tr><td><?= h($entry['username']) ?></td><td><?= $entry['action'] === 'start' ? 'Angemeldet' : 'Abgemeldet' ?></td><td><?= h($entry['actor_name']) ?></td><td><?= h(date('d.m.Y H:i', $entry['happened_at'])) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
