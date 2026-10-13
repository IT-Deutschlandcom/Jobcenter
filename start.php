<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
synchronize_user($user['id']);
$user = current_user();
$work = work_summary($user, time());
$payrollPeriod = (int) $user['rank_id'] >= 1 && (int) $user['rank_id'] <= 49
    ? active_payroll_period((int) $user['rank_id']) : null;
$periodMember = $payrollPeriod
    ? (payroll_period_summary($payrollPeriod['id'], $user['id'])['members'][0] ?? null) : null;
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
<div class="stats">
    <section class="card"><span>Deine erarbeiteten Stunden</span><strong class="rank-name" id="work-total"><?= h(duration_label($work['total_seconds'])) ?></strong><a href="hours.php">Stundenübersicht öffnen</a></section>
    <section class="card"><span>Lohn dieser Periode</span><strong id="work-payable"><?= $periodMember ? h(format_taler($periodMember['payable_cents'])) : '—' ?></strong><small><?= $periodMember ? '2,50 Taler je voller unbezahlter Stunde; Restminuten bleiben erhalten.' : ((int) $user['rank_id'] <= 49 ? 'Für deinen Rang läuft derzeit keine Lohnperiode.' : 'Vorgesetzte sind von der Mitarbeiter-Lohnberechnung ausgeschlossen.') ?></small></section>
    <section class="card"><span>Bereits ausgezahlt</span><strong id="work-paid"><?= $periodMember ? h(format_taler($periodMember['paid_cents'])) : '—' ?></strong><small>Auszahlungen werden durch Rang 65+ protokolliert.</small></section>
</div>
<div class="two-columns">
    <section class="card">
        <h2>Dein Dienst</h2>
        <p><span class="badge"><?= $user['active_since'] !== null ? 'Angemeldet · Zeit läuft' : 'Abgemeldet · Zeit pausiert' ?></span></p>
        <p>Nur angemeldete Dienstzeit zwischen 08:00 und 01:00 Uhr zählt. Restzeiten bleiben bei jeder Abmeldung erhalten; um 01:00 Uhr erfolgt die automatische Abmeldung.</p>
        <?php if ($user['active_since'] !== null) { action_form($user['id'], 'stop', 'Vom Dienst abmelden'); } ?>
        <p><small>Keine Selbsteintragung: Nur eine andere Führungskraft ab Squad Leader kann dich anmelden. Du kannst deinen laufenden Dienst selbst beenden.</small></p>
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
<?php if (can_view_payroll($user)): ?>
<section class="card"><h2>Director-Informationen</h2><p><?= (int) $stats['members'] ?> Mitarbeiter · <?= (int) $stats['active'] ?> im Dienst. Lohnstände, Restzeiten und Auszahlungsprotokolle stehen im geschützten Director-Panel bereit.</p><a class="button" href="payroll.php">Lohnverwaltung öffnen</a></section>
<?php endif; ?>
<?php page_footer(); ?>
