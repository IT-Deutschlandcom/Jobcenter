<?php
require_once __DIR__ . '/config.php';
require_csrf();

$username = trim(post_string('username'));
$password = post_string('password');
$confirmation = post_string('passverif');
$email = trim(post_string('email'));
$avatar = trim(post_string('avatar'));
$registrationError = '';

if ($username === '' || strlen($username) > 100) {
    $registrationError = 'Bitte gib einen Benutzernamen mit höchstens 100 Bytes ein.';
} elseif (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0")) {
    $registrationError = 'Das Passwort muss zwischen 8 und 72 Bytes lang sein.';
} elseif ($password !== $confirmation) {
    $registrationError = 'Die Passwörter stimmen nicht überein.';
} elseif (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $registrationError = 'Bitte gib eine gültige E-Mail-Adresse ein.';
} elseif ($avatar !== '' && safe_avatar_url($avatar) === '') {
    $registrationError = 'Der Avatar muss eine gültige HTTP- oder HTTPS-URL sein.';
} else {
    try {
        $statement = db()->prepare(
            'INSERT INTO users (username, password, email, avatar, signup_date) VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email, $avatar, time()]);
        header('Location: index.php?registered=1');
        exit;
    } catch (PDOException $exception) {
        $registrationError = ($exception->errorInfo[1] ?? null) === 1062
            ? 'Dieser Benutzername ist bereits vergeben.'
            : 'Die Registrierung ist derzeit nicht verfügbar. Bitte versuche es später erneut.';
    }
}
http_response_code(422);
require __DIR__ . '/register.php';
