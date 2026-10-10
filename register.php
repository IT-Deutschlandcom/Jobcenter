<?php
include('config.php');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSA - Registrierung</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div id="alert"><b>Hinweis:</b> BF-Toolnutzer ohne Gruppenmitgliedschaft: Bitte stellt eine Gruppenanfrage! <div id="x"><a href="index.php">Anmelden</a></div></div>
    <main class="auth-shell">
        <section class="auth-intro">
            <span class="brand-mark">CSA</span>
            <span class="eyebrow">CyberForce Security Agency</span>
            <h1>Deine Karriere<br>beginnt hier.</h1>
            <p>Werde Teil unseres Jobcenters im Habbo Hotel. Erstelle dein CSA-Konto und entdecke unser Team und deine Aufstiegsm&ouml;glichkeiten.</p>
            <a href="start.php">Zum Dashboard &rarr;</a>
        </section>
        <section class="login" aria-labelledby="register-title">
            <span class="eyebrow">Neu in der Agency?</span>
            <h2 id="register-title">Konto erstellen</h2>
            <form action="sign_up.php" method="post">
                <label for="username">Benutzername</label>
                <input type="text" name="username" id="username" autocomplete="username" placeholder="Dein Benutzername">
                <label for="password">Passwort</label>
                <input type="password" name="password" id="password" autocomplete="new-password" placeholder="Dein Passwort">
                <label for="passverif">Passwort best&auml;tigen</label>
                <input type="password" name="passverif" id="passverif" autocomplete="new-password" placeholder="Passwort wiederholen">
                <label for="email">E-Mail-Adresse</label>
                <input type="text" name="email" id="email" autocomplete="email" placeholder="Deine E-Mail-Adresse">
                <label for="avatar">Avatar <small>(optional)</small></label>
                <input type="text" name="avatar" id="avatar" placeholder="URL zu deinem Avatar">
                <input type="submit" value="Registrieren">
            </form>
            <p>Bereits dabei? <a href="index.php">Zur Anmeldung</a></p>
        </section>
    </main>
</body>
</html>
