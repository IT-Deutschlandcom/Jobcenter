<?php
require_once __DIR__ . '/config.php';
if (isset($_SESSION['userid']) && !isset($loginError) && current_user() !== null) {
    header('Location: start.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EURO - Anmeldung</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div id="alert"><b>Hinweis:</b> BF-Toolnutzer ohne Gruppenmitgliedschaft: Bitte stellt eine Gruppenanfrage! <div id="x"><a href="register.php">Registrieren</a></div></div>
    <main class="auth-shell">
        <section class="auth-intro">
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
</body>
</html>
