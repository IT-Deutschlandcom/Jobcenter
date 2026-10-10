<?php
require_once __DIR__ . '/config.php';
$user = require_login();
require_once __DIR__ . '/layout.php';
$members = db()->query('SELECT id, username, signup_date FROM users ORDER BY username');
page_header('Mitglieder', $user);
?>
<section class="panel">
    <h1>EURO Mitglieder</h1>
    <table>
        <thead><tr><th>Benutzername</th><th>Mitglied seit</th></tr></thead>
        <tbody>
        <?php foreach ($members as $member): ?>
            <tr>
                <td><a href="profile.php?id=<?= h($member['id']) ?>"><?= h($member['username']) ?></a></td>
                <td><?= h(date('d.m.Y', (int) $member['signup_date'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php page_footer(); ?>
