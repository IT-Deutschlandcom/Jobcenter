<?php
require_once __DIR__ . '/promotion_service.php';
$user = require_login();
require_csrf();
try {
    duty_action((int) $user['id'], (int) post_string('user_id'),
        post_string('action'), (int) post_string('promotion_id'));
    $_SESSION['flash'] = 'Aktion erfolgreich gespeichert.';
} catch (DomainException $error) {
    $_SESSION['flash'] = $error->getMessage();
} catch (Throwable $error) {
    error_log('EURO duty action failed: ' . $error->getMessage());
    $_SESSION['flash'] = 'Die Aktion konnte nicht gespeichert werden. Bitte erneut versuchen.';
}
header('Location: ' . (can_manage($user) ? 'bf.php' : 'start.php'));
exit;
