<?php
require_once __DIR__ . '/payroll_service.php';

function nav_icon($file)
{
    if ($file === null) {
        return '<span class="nav-icon nav-icon-empty" aria-hidden="true"></span>';
    }
    return '<img class="nav-icon" src="img/' . h($file) . '" alt="" width="28" height="28">';
}

function page_header($title, $user)
{
    ?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> – EURO Tool</title>
    <?php bootstrap_assets(); ?>
    <link rel="stylesheet" href="css/euro.css">
    <script src="js/promotions.js" defer></script>
    <script src="js/effects.js" defer></script>
</head>
<body class="dark-ui">
<canvas id="fx-particles" aria-hidden="true"></canvas>
<div class="fx-aurora" aria-hidden="true"></div>
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
            $icons = ['start.php' => 'dashboard.png', 'profile.php' => 'community.png', 'hours.php' => 'date.png',
                'rangliste.php' => 'ranks.gif', 'news.php' => 'news.gif', 'bf.php' => 'key.png',
                'payroll.php' => 'moneybar_icon_coin.png'];
            foreach ($links as $url => $label) {
                $script = strtok($url, '?');
                $active = basename($_SERVER['SCRIPT_NAME']) === $script;
                echo '<a href="' . h($url) . '"' . ($active ? ' class="active" aria-current="page"' : '') . '>'
                    . nav_icon($icons[$script] ?? null) . '<span>' . h($label) . '</span></a>';
            }
            ?>
        </nav>
        <div class="sidebar-user">
            <strong><?= h($user['username']) ?></strong>
            <small><?= h(rank_label($user)) ?></small>
            <form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button class="secondary logout-button"><?= nav_icon('logout.png') ?><span>Konto abmelden</span></button></form>
        </div>
    </aside>
    <main class="main-content">
        <div class="banner-wrap"><img class="site-banner" src="img/Banner.png" alt="EURO – Europol Jobcenter" width="1016" height="185"></div>
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
<?php bootstrap_scripts(); ?>
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
    <button<?= $action === 'stop' ? ' class="secondary"' : '' ?>><?php if ($action === 'start'): ?><img class="btn-icon" src="img/like.png" alt="" width="20" height="20"><?php endif; ?><span><?= h($label) ?></span></button>
</form>
    <?php
}
