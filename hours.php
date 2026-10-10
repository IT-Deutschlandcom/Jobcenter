<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
if (!can_manage($user)) {
    http_response_code(403);
    exit('Die Stundenübersicht ist erst ab Rang 50 sichtbar.');
}
synchronize_visible_users($user);
$members = service_query('SELECT id, username, rank_id, rank_step, total_seconds, promotion_seconds, active_since FROM users ORDER BY total_seconds DESC, username')->fetchAll();
page_header('Gesammelte Stunden', $user);
?>
<section class="card">
    <h2>Dienstzeit-Rangliste</h2>
    <p>Gesamte angemeldete Zeit über alle Dienste. Beförderungen verbrauchen nur das Zeitguthaben, niemals die Gesamtstunden.</p>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Rang</th><th>Gesammelte Zeit</th><th>Beförderungsguthaben</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($members as $member): ?>
        <tr><td><?= h($member['username']) ?></td><td><?= h(rank_label($member)) ?></td><td><?= (int) floor($member['total_seconds'] / 3600) ?> h <?= (int) floor($member['total_seconds'] % 3600 / 60) ?> min</td><td><?= (int) floor($member['promotion_seconds'] / 3600) ?> h <?= (int) floor($member['promotion_seconds'] % 3600 / 60) ?> min</td><td><?= $member['active_since'] === null ? 'Abgemeldet' : 'Im Dienst' ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
