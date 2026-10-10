<?php
require_once __DIR__ . '/config.php';
require_csrf();
$user = require_login();
require_once __DIR__ . '/promotion_service.php';
try {
    if ($user['active_since'] !== null) {
        duty_action($user['id'], $user['id'], 'stop');
    }
} catch (Throwable $error) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    exit('Die Abmeldung konnte nicht abgeschlossen werden. Deine Sitzung bleibt erhalten, damit keine Dienstzeit verloren geht. Bitte versuche es erneut. <a href="start.php">Zum Dashboard</a>');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parameters['path'],
        'domain' => $parameters['domain'],
        'secure' => $parameters['secure'],
        'httponly' => $parameters['httponly'],
        'samesite' => $parameters['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
header('Location: index.php');
exit;
