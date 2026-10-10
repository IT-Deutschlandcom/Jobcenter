<?php
require_once __DIR__ . '/config.php';
require_csrf();

$loginUsername = trim(post_string('username'));
$password = post_string('password');
$loginError = 'Benutzername oder Passwort ist falsch.';

try {
    $statement = db()->prepare('SELECT id, username, password FROM users WHERE username = ?');
    $statement->execute([$loginUsername]);
    $account = $statement->fetch();
    if ($account && $password !== '') {
        $stored = (string) $account['password'];
        $isHash = password_get_info($stored)['algo'] !== null;
        $valid = $isHash ? password_verify($password, $stored) : hash_equals($stored, $password);
        if ($valid) {
            if (!$isHash || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $update = db()->prepare('UPDATE users SET password = ? WHERE id = ? AND password = ?');
                $update->execute([password_hash($password, PASSWORD_DEFAULT), $account['id'], $stored]);
            }
            session_regenerate_id(true);
            $_SESSION['userid'] = $account['id'];
            $_SESSION['username'] = $account['username'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: start.php');
            exit;
        }
    }
} catch (PDOException $exception) {
    $loginError = 'Die Anmeldung ist derzeit nicht verfügbar. Bitte versuche es später erneut.';
}
http_response_code(422);
require __DIR__ . '/index.php';
