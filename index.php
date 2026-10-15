<?php
require_once __DIR__ . '/config.php';
if (isset($_SESSION['userid']) && !isset($loginError) && current_user() !== null) {
    header('Location: start.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EURO - Anmeldung</title>
    <?php bootstrap_assets(); ?>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/effects.js" defer></script>
</head>
<body class="dark-ui auth-page">
    <canvas id="fx-particles" aria-hidden="true"></canvas>
    <div class="fx-aurora" aria-hidden="true"></div>
    <div id="alert"><b>Hinweis:</b> BF-Toolnutzer ohne Gruppenmitgliedschaft: Bitte stellt eine Gruppenanfrage! <div id="x"><a href="register.php">Registrieren</a></div></div>
    <header class="banner-wrap auth-banner"><img class="site-banner" src="img/Banner.png" alt="EURO – Europol Jobcenter" width="1016" height="185"></header>
    <main class="auth-shell">
        <section class="auth-intro">
            <img class="hero-logo" src="img/Europol-logo.png" alt="Europol Logo" width="1536" height="1024">
            <span class="brand-mark">EURO</span>
            <span class="eyebrow">Europol</span>
            <h1>Dein Jobcenter.<br>Dein Team. Dein Hotel.</h1>
            <p>Willkommen in der EURO Staff Area. Hier findest du Neuigkeiten, R&auml;nge und Informationen rund um unser Jobcenter im Habbo Hotel.</p>
            <a href="start.php">Zum Dashboard &rarr;</a>
        </section>
        <section class="login" aria-labelledby="login-title">
            <span class="eyebrow">Mitarbeiterbereich</span>
            <h2 id="login-title">Willkommen zur&uuml;ck!</h2>
            <p>Melde dich mit deinem EURO-Konto an.</p>
            <?php if (!empty($loginError)): ?>
                <p role="alert"><?= h($loginError) ?></p>
            <?php endif; ?>
            <?php if (($_GET['registered'] ?? '') === '1'): ?>
                <p role="status">Erfolgreich registriert! Du kannst dich jetzt anmelden.</p>
            <?php endif; ?>
            <form action="connexion.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <label for="username">Benutzername</label>
                <input type="text" name="username" id="username" autocomplete="username" placeholder="Dein Benutzername" maxlength="100" required value="<?= h($loginUsername ?? '') ?>">
                <label for="password">Passwort</label>
                <input type="password" name="password" id="password" autocomplete="current-password" placeholder="Dein Passwort" required>
                <input type="submit" value="Anmelden">
            </form>
            <p>Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>
        </section>
    </main>
    <?php bootstrap_scripts(); ?>
</body>
</html>
