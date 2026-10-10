<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
page_header('Handbuch', $user);
?>
<section class="card">
    <h2>Dienst & Beförderungen</h2>
    <p>Melde dich zuerst mit deinem EURO-Konto an und anschließend im Dashboard zum Dienst.
    Ab Rang 50 Squad Leader kann die Führung Mitarbeiter ebenfalls an- und abmelden.</p>
    <p>Angemeldete Zeit wird serverseitig addiert, auch wenn der Browser geschlossen ist.
    Melde dich daher nach Dienstende ausdrücklich vom Dienst ab. Der Konto-Logout beendet deinen Dienst ebenfalls.</p>
    <p>Alle 60 Dienstminuten entsteht eine Beförderung zur Bestätigung. Nicht verbrauchte Zeit und Restminuten
    bleiben erhalten; jede Beförderung verbraucht eine Stunde Guthaben. Die Gesamtstunden bleiben unverändert.</p>
    <p>Die Führung bestätigt im BF-Panel. Das Protokoll hält fest, wer die Dienstanmeldung vorgenommen hat,
    wann der automatische Vorschlag erstellt wurde und wer ihn bestätigt hat.</p>
    <h2>Öffnungszeiten</h2>
    <p>Anmeldeschluss: Sonntag–Donnerstag 21:00 Uhr, Freitag und Samstag 22:00 Uhr.
    Diese Zeiten sind Hinweise und keine automatische Abmeldung.</p>
    <a class="button" href="rangliste.php">Ränge & Berechtigungen ansehen</a>
</section>
<?php page_footer(); ?>
