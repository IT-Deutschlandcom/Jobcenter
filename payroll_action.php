<?php
require_once __DIR__ . '/payroll_service.php';
$user = require_login();
require_csrf();
if (!can_view_payroll($user)) {
    http_response_code(403);
    exit('Keine Berechtigung zur Lohnverwaltung.');
}
try {
    if (post_string('delivered') !== '1') {
        throw new DomainException('Bitte die tatsächliche Übergabe der Taler bestätigen.');
    }
    $taler = record_payout((int) $user['id'], (int) post_string('user_id'),
        (int) post_string('paid_seconds'), (int) post_string('taler'));
    $_SESSION['flash'] = $taler . ' Taler als ausgezahlt protokolliert.';
} catch (DomainException $error) {
    $_SESSION['flash'] = $error->getMessage();
} catch (Throwable $error) {
    error_log('EURO payout failed: ' . $error->getMessage());
    $_SESSION['flash'] = 'Die Auszahlung konnte nicht gespeichert werden. Bitte erneut versuchen.';
}
header('Location: payroll.php');
exit;
