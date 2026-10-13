<?php
require_once __DIR__ . '/payroll_service.php';

function page_header($title, $user)
{
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> – EURO Tool</title>
    <link rel="stylesheet" href="css/euro.css">
    <script src="js/promotions.js" defer></script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="start.php"><span class="brand-icon">EU</span><span>EURO <small>Europol · Habbo Jobcenter</small></span></a>
        <nav aria-label="Hauptnavigation">
            <?php
            $links = ['start.php' => 'Dashboard', 'profile.php?id=' . $user['id'] => 'Mein Profil',
                'hours.php' => 'Meine Stunden & Lohn',
                'rangliste.php' => 'Ränge & Karriere', 'news.php' => 'News', 'ranks.php' => 'Führungsebene',
                'sonstiges.php' => 'Handbuch'];
            if (can_manage($user)) {
                $links['bf.php'] = 'BF-Panel';
                $links['hours.php'] = 'Gesammelte Stunden';
            }
            if (can_view_payroll($user)) {
                $links['payroll.php'] = 'Lohnberechnung';
                $links['payroll_periods.php'] = 'Lohnperioden';
            }
            foreach ($links as $url => $label) {
                $active = basename($_SERVER['SCRIPT_NAME']) === strtok($url, '?');
                echo '<a href="' . h($url) . '"' . ($active ? ' class="active" aria-current="page"' : '') . '>' . h($label) . '</a>';
            }
            ?>
        </nav>
        <div class="sidebar-user">
            <strong><?= h($user['username']) ?></strong>
            <small><?= h(rank_label($user)) ?></small>
            <form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button class="secondary">Konto abmelden</button></form>
        </div>
    </aside>
    <main class="main-content">
        <header class="page-header"><div><span class="eyebrow">EURO Mitarbeiterbereich</span><h1><?= h($title) ?></h1></div><span class="badge"><?= can_manage($user) ? 'Führung · BF-Rechte' : 'Mitglied' ?></span></header>
        <?php if (isset($_SESSION['flash'])): ?>
            <p role="status" class="notice"><?= h($_SESSION['flash']) ?></p>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
    <?php
}

function promotion_popup()
{
    ?>
<dialog id="promotion-popup" aria-labelledby="promotion-title" data-csrf="<?= h(csrf_token()) ?>">
    <span class="eyebrow">Karriere-Update</span>
    <h2 id="promotion-title">Beförderungszeit!</h2>
    <p>Eine volle Dienststunde ist erreicht. Die Beförderung wartet auf Bestätigung durch die Führung.</p>
    <ul id="promotion-list"></ul>
    <p id="promotion-error" role="alert"></p>
    <button id="promotion-close" class="secondary" type="button">Schließen</button>
</dialog>
    <?php
}

function page_footer()
{
    ?>
    <footer>EURO · Europol im Habbo Hotel · Kein offizielles Angebot von Europol oder Habbo.</footer>
    </main>
</div>
<?php promotion_popup(); ?>
</body>
</html>
    <?php
}

function action_form($target, $action, $label, $promotion = 0)
{
    ?>
<form class="inline-form" method="post" action="bf_action.php">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="user_id" value="<?= (int) $target ?>">
    <input type="hidden" name="action" value="<?= h($action) ?>">
    <input type="hidden" name="promotion_id" value="<?= (int) $promotion ?>">
    <?php if (in_array($action, ['start', 'stop'], true) && can_manage(current_user())): ?>
        <label>Bonus-/Strafminuten
            <input type="number" name="adjustment_minutes" min="-1440" max="1440" step="1" value="0">
        </label>
    <?php endif; ?>
    <button<?= $action === 'stop' ? ' class="secondary"' : '' ?>><?= h($label) ?></button>
</form>
    <?php
}
