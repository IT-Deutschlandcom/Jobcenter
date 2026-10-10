<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
page_header('Ränge & Karriere', $user);
?>
<section class="card">
    <h2>Dein Weg bei EURO</h2>
    <p>TAC steigt pro bestätigter Dienststunde einen Rang auf, bis Rang 19 Senior Operations Agents.
    Danach beginnt Intel bei Investigator 0/10. Jede Stunde erhöht die Stufe um eins.
    Nach 10/10 folgt mit der nächsten bestätigten Stunde der nächste Intel-Rang bei 0/10.
    Rang 49 Europol Elite ist das automatische Karriereziel.</p>
    <p>Ab Rang 50 Squad Leader: BF-Panel, Dienstverwaltung für andere Mitarbeiter, Beförderungsbestätigung,
    Stundenübersicht sowie Beförderungs- und Dienstprotokoll. Führungsrechte werden nicht automatisch erworben.</p>
    <div class="table-scroll"><table><thead><tr><th>Rang</th><th>Bezeichnung</th><th>Bereich</th><th>Stufen / Zeit</th></tr></thead><tbody>
    <?php foreach (rank_catalog() as $id => $rank): ?>
        <tr><td><?= (int) $id ?></td><td><?= h($rank['name']) ?></td><td><?= h($rank['division']) ?></td><td><?= $id >= 50 ? 'Administrative Ernennung' : ($id === 49 ? 'Karriereziel' : ($rank['steps'] ? '0/10 bis 10/10 · 60 Minuten je Schritt' : '60 Minuten je Rang')) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php page_footer(); ?>
