<?php
require_once __DIR__ . '/config.php';
$user = require_login();
require_once __DIR__ . '/layout.php';

$id = $_GET['id'] ?? $user['id'];
$profile = false;
if ((is_string($id) || is_int($id)) && ctype_digit((string) $id) && (int) $id > 0) {
    $statement = db()->prepare('SELECT id, username, avatar, signup_date FROM users WHERE id = ?');
    $statement->execute([$id]);
    $profile = $statement->fetch();
}
if (!$profile) {
    http_response_code(404);
}
page_header('Mitgliederprofil', $user);
?>
<section class="panel">
    <?php if (!$profile): ?>
        <h1>Mitglied nicht gefunden</h1>
    <?php else: ?>
        <h1><?= h($profile['username']) ?></h1>
        <?php $avatar = safe_avatar_url($profile['avatar']); ?>
        <?php if ($avatar !== ''): ?>
            <img src="<?= h($avatar) ?>" alt="Avatar" width="100" height="100" referrerpolicy="no-referrer">
        <?php endif; ?>
        <p>Bei Europol seit <?= h(date('d.m.Y', (int) $profile['signup_date'])) ?></p>
        <?php if ((string) $profile['id'] === (string) $user['id']): ?>
            <p>E-Mail: <?= h($user['email']) ?></p>
            <a href="edit_infos.php">Profil bearbeiten</a>
        <?php endif; ?>
    <?php endif; ?>
    <p><a href="users.php">Zur Mitgliederliste</a></p>
</section>
<?php page_footer(); ?>
