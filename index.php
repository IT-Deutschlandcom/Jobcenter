<?php
include('config.php');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSA - Anmeldung</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div id="alert"><b>Hinweis:</b> BF-Toolnutzer ohne Gruppenmitgliedschaft: Bitte stellt eine Gruppenanfrage! <div id="x"><a href="register.php">Registrieren</a></div></div>
    <main class="auth-shell">
        <section class="auth-intro">
            <span class="brand-mark">CSA</span>
            <span class="eyebrow">CyberForce Security Agency</span>
            <h1>Dein Jobcenter.<br>Dein Team. Dein Hotel.</h1>
            <p>Willkommen in der CSA Staff Area. Hier findest du Neuigkeiten, R&auml;nge und Informationen rund um unser Jobcenter im Habbo Hotel.</p>
            <a href="start.php">Zum Dashboard &rarr;</a>
        </section>
        <section class="login" aria-labelledby="login-title">
            <span class="eyebrow">Mitarbeiterbereich</span>
            <h2 id="login-title">Willkommen zur&uuml;ck!</h2>
            <p>Melde dich mit deinem CSA-Konto an.</p>
            <form action="connexion.php" method="post">
                <label for="username">Benutzername</label>
                <input type="text" name="username" id="username" autocomplete="username" placeholder="Dein Benutzername">
                <label for="password">Passwort</label>
                <input type="password" name="password" id="password" autocomplete="current-password" placeholder="Dein Passwort">
                <input type="submit" value="Anmelden">
            </form>
            <p>Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>
        </section>
    </main>
</body>
</html>
