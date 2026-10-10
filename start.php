<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
synchronize_user($user['id']);
$user = current_user();
$stats = service_query('SELECT COUNT(*) AS members, SUM(active_since IS NOT NULL) AS active FROM users')->fetch();
page_header('Dashboard', $user);
?>
<section class="welcome">
    <span class="eyebrow">Gemeinsam weiterkommen</span>
    <h2>Willkommen zurück, <?= h($user['username']) ?>!</h2>
    <p>Dein Dienst, deine Karriere und dein Team – alles an einem Ort.</p>
</section>
<div class="stats">
    <section class="card"><span>Mitarbeiter gesamt</span><strong><?= (int) $stats['members'] ?></strong></section>
    <section class="card"><span>Im Dienst</span><strong><?= (int) $stats['active'] ?></strong></section>
    <section class="card"><span>Dein Rang</span><strong class="rank-name"><?= h(rank_label($user)) ?></strong></section>
</div>
<div class="two-columns">
    <section class="card">
        <h2>Dein Dienst</h2>
        <p><span class="badge"><?= $user['active_since'] !== null ? 'Angemeldet · Zeit läuft' : 'Abgemeldet · Zeit pausiert' ?></span></p>
        <p>Nur angemeldete Dienstzeit zählt. Restzeiten bleiben bei jeder Abmeldung erhalten.</p>
        <?php action_form($user['id'], $user['active_since'] === null ? 'start' : 'stop', $user['active_since'] === null ? 'Zum Dienst anmelden' : 'Vom Dienst abmelden'); ?>
        <p><small>Konto-Login und Dienstanmeldung sind getrennt. Auch die Führung kann deinen Dienst verwalten.</small></p>
    </section>
    <section class="card">
        <h2>Nächste Beförderung</h2>
        <?php if (next_rank($user)): ?>
            <p><?= h(rank_label(next_rank($user))) ?></p>
            <progress max="3600" value="<?= min(3600, (int) $user['promotion_seconds']) ?>"></progress>
            <p><?= (int) floor($user['promotion_seconds'] / 60) ?> Minuten verfügbar · je 60 Minuten eine Beförderung</p>
            <p>Jede Beförderung wird einzeln bestätigt. Intel durchläuft 0/10 bis 10/10.</p>
        <?php else: ?>
            <p>Automatisches Karriereziel erreicht. Führungsränge werden durch die Administration vergeben.</p>
        <?php endif; ?>
    </section>
</div>
<?php if (can_manage($user)): ?>
<section class="card"><h2>Dein Führungsbereich</h2><p>Dienstzeiten verwalten, Beförderungen bestätigen und das Protokoll prüfen.</p><a class="button" href="bf.php">BF-Panel öffnen</a> <a class="button secondary" href="hours.php">Stundenübersicht</a></section>
<?php endif; ?>
<?php page_footer(); ?>
