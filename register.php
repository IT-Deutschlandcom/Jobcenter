<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EURO - Registrierung</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div id="alert"><b>Hinweis:</b> BF-Toolnutzer ohne Gruppenmitgliedschaft: Bitte stellt eine Gruppenanfrage! <div id="x"><a href="index.php">Anmelden</a></div></div>
    <main class="auth-shell">
        <section class="auth-intro">
            <span class="brand-mark">EURO</span>
            <span class="eyebrow">Europol</span>
            <h1>Deine Karriere<br>beginnt hier.</h1>
            <p>Werde Teil unseres Jobcenters im Habbo Hotel. Erstelle dein EURO-Konto und entdecke unser Team und deine Aufstiegsm&ouml;glichkeiten.</p>
            <a href="start.php">Zum Dashboard &rarr;</a>
        </section>
        <section class="login" aria-labelledby="register-title">
            <span class="eyebrow">Neu bei Europol?</span>
            <h2 id="register-title">Konto erstellen</h2>
            <?php if (!empty($registrationError)): ?>
                <p role="alert"><?= h($registrationError) ?></p>
            <?php endif; ?>
            <form action="sign_up.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <label for="username">Benutzername</label>
                <input type="text" name="username" id="username" autocomplete="username" placeholder="Dein Benutzername" maxlength="100" required value="<?= h($username ?? '') ?>">
                <label for="password">Passwort</label>
                <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Mindestens 8 Zeichen" minlength="8" maxlength="72" required>
                <label for="passverif">Passwort best&auml;tigen</label>
                <input type="password" name="passverif" id="passverif" autocomplete="new-password" placeholder="Passwort wiederholen" required>
                <label for="email">E-Mail-Adresse</label>
                <input type="email" name="email" id="email" autocomplete="email" placeholder="Deine E-Mail-Adresse" maxlength="255" required value="<?= h($email ?? '') ?>">
                <label for="avatar">Avatar <small>(optional)</small></label>
                <input type="url" name="avatar" id="avatar" placeholder="https://..." value="<?= h($avatar ?? '') ?>">
                <input type="submit" value="Registrieren">
            </form>
            <p>Bereits dabei? <a href="index.php">Zur Anmeldung</a></p>
        </section>
    </main>
</body>
</html>
