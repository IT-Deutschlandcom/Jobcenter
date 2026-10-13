<?php
require_once __DIR__ . '/payroll_service.php';
$user = require_login();
require_csrf();
try {
    if (post_string('action') === 'start') {
        start_payroll_period((int) $user['id'], (int) post_string('rank_id'));
        $_SESSION['flash'] = 'Die Lohnperiode wurde gestartet.';
    } elseif (post_string('action') === 'end') {
        end_payroll_period((int) $user['id'], (int) post_string('period_id'));
        $_SESSION['flash'] = 'Die Lohnperiode wurde beendet.';
    } else {
        throw new DomainException('Unbekannte Periodenaktion.');
    }
} catch (DomainException $error) {
    $_SESSION['flash'] = $error->getMessage();
} catch (Throwable $error) {
    error_log('EURO payroll period action failed: ' . $error->getMessage());
    $_SESSION['flash'] = 'Die Lohnperiode konnte nicht gespeichert werden. Bitte erneut versuchen.';
}
header('Location: payroll_periods.php');
exit;
