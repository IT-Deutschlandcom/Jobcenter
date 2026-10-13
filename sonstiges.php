<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
page_header('Handbuch', $user);
?>
<section class="card">
    <h2>Dienst & Beförderungen</h2>
    <p>Melde dich mit deinem EURO-Konto an und bitte eine Führungskraft ab Rang 50 Squad Leader um Dienstanmeldung.
    Nur Mitarbeitende der Ränge 1 bis 49 können zum Dienst angemeldet werden. Selbsteintragungen sind gesperrt;
    deinen laufenden Dienst kannst du selbst beenden.</p>
    <p>Angemeldete Zeit wird serverseitig addiert, auch wenn der Browser geschlossen ist.
    Öffnungszeiten sind täglich 08:00 bis 01:00 Uhr (Europe/Berlin); um 01:00 Uhr wirst du automatisch abgemeldet.
    Der Konto-Logout beendet deinen Dienst ebenfalls.</p>
    <p>Alle 60 Dienstminuten entsteht eine Beförderung zur Bestätigung. Nicht verbrauchte Zeit und Restminuten
    bleiben erhalten; jede Beförderung verbraucht eine Stunde Guthaben. Die Gesamtstunden bleiben unverändert.</p>
    <p>Die Führung bestätigt im BF-Panel. Das Protokoll hält fest, wer die Dienstanmeldung vorgenommen hat,
    wann der automatische Vorschlag erstellt wurde und wer ihn bestätigt hat.</p>
    <h2>Stunden & Lohn</h2>
    <p>Der festgelegte Grundlohn beträgt 2,50 Taler je Stunde für Ränge 1 bis 49 und 3 Taler je Stunde ab Rang 50.
    Da Vorgesetzte nicht in die Mitarbeiter-Lohnberechnung aufgenommen werden, gilt der zweite Satz nicht für die
    Lohnperioden im Tool. Eine Führungskraft ab Rang 65 kann
    für jeden Rang einzeln eine Lohnperiode starten und beenden. Laufende Perioden können bereits berechnet werden;
    vor Periodenstart geleistete Zeit wird nicht rückwirkend angerechnet. Restminuten bleiben innerhalb der Periode.</p>
    <p>Bonus- oder Strafminuten kann eine Führungskraft beim An- und Abmelden setzen. Diese Änderungen werden
    mit Mitarbeiter, Führungskraft, Minuten und Zeitpunkt protokolliert. Auszahlungen bestätigt die Führungskraft
    erst nach tatsächlicher Talerübergabe; dein persönliches Protokoll findest du in deiner Stundenübersicht.</p>
    <p>Vorgesetzte werden weder zum Mitarbeiterdienst noch zur Mitarbeiter-Lohnberechnung angemeldet. Ihre BF-Zeit
    wird separat sonntags addiert; die Führung muss die erforderliche Stundenschwelle für BF festlegen.</p>
    <a class="button" href="rangliste.php">Ränge & Berechtigungen ansehen</a>
</section>
<?php page_footer(); ?>
