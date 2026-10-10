<?php
require_once __DIR__ . '/config.php';
$user = require_login();
require_once __DIR__ . '/layout.php';

$username = (string) $user['username'];
$email = (string) $user['email'];
$avatar = safe_avatar_url($user['avatar']);
$error = '';
$message = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_csrf();
    $username = trim(post_string('username'));
    $email = trim(post_string('email'));
    $avatar = trim(post_string('avatar'));
    $password = post_string('password');
    $confirmation = post_string('passverif');
    if ($username === '' || strlen($username) > 100) {
        $error = 'Bitte gib einen Benutzernamen mit höchstens 100 Bytes ein.';
    } elseif (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte gib eine gültige E-Mail-Adresse ein.';
    } elseif ($avatar !== '' && safe_avatar_url($avatar) === '') {
        $error = 'Der Avatar muss eine gültige HTTP- oder HTTPS-URL sein.';
    } elseif ($password !== $confirmation) {
        $error = 'Die Passwörter stimmen nicht überein.';
    } elseif ($password !== '' && (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0"))) {
        $error = 'Das Passwort muss zwischen 8 und 72 Bytes lang sein.';
    } else {
        try {
            $parameters = [$username, $email, $avatar];
            $query = 'UPDATE users SET username = ?, email = ?, avatar = ?';
            if ($password !== '') {
                $query .= ', password = ?';
                $parameters[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $query .= ' WHERE id = ?';
            $parameters[] = $user['id'];
            db()->prepare($query)->execute($parameters);
            session_regenerate_id(true);
            $_SESSION['username'] = $username;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $user = require_login();
            $message = 'Deine Angaben wurden gespeichert.';
        } catch (PDOException $exception) {
            $error = ($exception->errorInfo[1] ?? null) === 1062
                ? 'Dieser Benutzername ist bereits vergeben.'
                : 'Deine Angaben konnten nicht gespeichert werden.';
        }
    }
    if ($error !== '') {
        http_response_code(422);
    }
}
page_header('Profil bearbeiten', $user);
?>
<section class="panel">
    <h1>Profil bearbeiten</h1>
    <?php if ($error !== ''): ?><p role="alert"><?= h($error) ?></p><?php endif; ?>
    <?php if ($message !== ''): ?><p role="status"><?= h($message) ?></p><?php endif; ?>
    <form action="edit_infos.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <label for="username">Benutzername</label>
        <input type="text" name="username" id="username" value="<?= h($username) ?>" maxlength="100" required autocomplete="username">
        <label for="email">E-Mail-Adresse</label>
        <input type="email" name="email" id="email" value="<?= h($email) ?>" maxlength="255" required autocomplete="email">
        <label for="avatar">Avatar (HTTP/HTTPS, optional)</label>
        <input type="url" name="avatar" id="avatar" value="<?= h($avatar) ?>">
        <label for="password">Neues Passwort (leer lassen, um es beizubehalten)</label>
        <input type="password" name="password" id="password" minlength="8" maxlength="72" autocomplete="new-password">
        <label for="passverif">Neues Passwort bestätigen</label>
        <input type="password" name="passverif" id="passverif" autocomplete="new-password">
        <button type="submit">Speichern</button>
    </form>
</section>
<?php page_footer(); ?>
