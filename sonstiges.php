<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
page_header('Handbuch', $user);
?>
<section class="card">
    <h2>Dienst & Beförderungen</h2>
    <p>Melde dich mit deinem EURO-Konto an und bitte eine Führungskraft ab Rang 50 Squad Leader um Dienstanmeldung.
    Selbsteintragungen sind für alle Ränge gesperrt. Deinen laufenden Dienst kannst du selbst beenden.</p>
    <p>Angemeldete Zeit wird serverseitig addiert, auch wenn der Browser geschlossen ist.
    Melde dich daher nach Dienstende ausdrücklich vom Dienst ab. Der Konto-Logout beendet deinen Dienst ebenfalls.</p>
    <p>Alle 60 Dienstminuten entsteht eine Beförderung zur Bestätigung. Nicht verbrauchte Zeit und Restminuten
    bleiben erhalten; jede Beförderung verbraucht eine Stunde Guthaben. Die Gesamtstunden bleiben unverändert.</p>
    <p>Die Führung bestätigt im BF-Panel. Das Protokoll hält fest, wer die Dienstanmeldung vorgenommen hat,
    wann der automatische Vorschlag erstellt wurde und wer ihn bestätigt hat.</p>
    <h2>Stunden & Lohn</h2>
    <p>Deine erarbeiteten Stunden und deinen voraussichtlichen Lohn siehst du im Dashboard und unter „Meine Stunden & Lohn“.
    Jede volle unbezahlte Stunde ergibt 4 Taler. Restminuten bleiben für die nächste Auszahlung erhalten;
    Beförderungen kürzen den Lohn nicht.</p>
    <p>Director-Ränge sehen die Lohnstände aller Mitarbeiter in einem eigenen Panel und bestätigen dort tatsächlich
    übergebene Auszahlungen. Dein persönliches Auszahlungsprotokoll findest du in deiner Stundenübersicht.</p>
    <h2>Öffnungszeiten</h2>
    <p>Anmeldeschluss: Sonntag–Donnerstag 21:00 Uhr, Freitag und Samstag 22:00 Uhr.
    Diese Zeiten sind Hinweise und keine automatische Abmeldung.</p>
    <a class="button" href="rangliste.php">Ränge & Berechtigungen ansehen</a>
</section>
<?php page_footer(); ?>
