<?php
require_once dirname(__DIR__) . '/promotion_service.php';
if (PHP_SAPI !== 'cli' || getenv('EURO_DB_NAME') !== 'euro_test') {
    exit("Nur CLI mit separater Datenbank EURO_DB_NAME=euro_test.\n");
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function denied($operation)
{
    try {
        $operation();
    } catch (DomainException $expected) {
        return;
    }
    throw new RuntimeException('Unberechtigte/doppelte Aktion wurde zugelassen.');
}

$ids = [];
try {
    foreach ([50, 1, 20] as $rank) {
        service_query('INSERT INTO users (username, password, email, avatar, signup_date, rank_id) VALUES (?, ?, ?, ?, ?, ?)',
            ['test_' . bin2hex(random_bytes(8)), password_hash('test-only-passphrase', PASSWORD_DEFAULT), 'test@example.invalid', '', time(), $rank]);
        $ids[] = (int) db()->lastInsertId();
    }
    [$leader, $member, $intel] = $ids;
    $get = fn($id) => service_query('SELECT * FROM users WHERE id = ?', [$id])->fetch();
    check(count(rank_catalog()) === 55, 'Rangkatalog unvollständig.');
    check(!can_manage($get($member)) && can_manage($get($leader)), 'Führungsgrenze falsch.');
    foreach ([[19, 0, 20, 0], [20, 0, 20, 1], [20, 10, 21, 0], [48, 10, 49, 0]] as $case) {
        check(next_rank(['rank_id' => $case[0], 'rank_step' => $case[1]]) === ['rank_id' => $case[2], 'rank_step' => $case[3]], 'Rangübergang falsch.');
    }
    check(next_rank(['rank_id' => 49, 'rank_step' => 0]) === null, 'Führungsrechte automatisch vergeben.');
    check(earned_seconds(['active_since' => null], time()) === 0, 'Offlinezeit wurde gezählt.');
    denied(fn() => duty_action($member, $intel, 'start'));
    duty_action($member, $member, 'start');
    denied(fn() => duty_action($leader, $member, 'start'));
    service_query('UPDATE users SET active_since = ? WHERE id = ?', [time() - 1800, $member]);
    duty_action($leader, $member, 'stop');
    $first = $get($member);
    check($first['total_seconds'] >= 1800 && $first['total_seconds'] < 1810, 'Erster Dienst falsch addiert.');
    check($first['active_since'] === null, 'Dienst nicht beendet.');
    denied(fn() => duty_action($leader, $member, 'stop'));
    duty_action($leader, $member, 'start');
    service_query('UPDATE users SET active_since = ? WHERE id = ?', [time() - 5417, $member]);
    duty_action($leader, $member, 'stop');
    $saved = $get($member);
    check($saved['total_seconds'] >= 7217 && $saved['total_seconds'] < 7230, 'Restzeit nicht über Dienste addiert.');
    synchronize_user($member);
    synchronize_user($member);
    $pending = pending_promotions($saved);
    check(count($pending) === 1, 'Doppelter offener Vorschlag.');
    check((int) $pending[0]['created_by'] === $leader, 'Ersteller/Dienstanmelder fehlt.');
    denied(fn() => duty_action($member, $member, 'confirm', $pending[0]['id']));
    duty_action($leader, $member, 'confirm', $pending[0]['id']);
    denied(fn() => duty_action($leader, $member, 'confirm', $pending[0]['id']));
    $advanced = $get($member);
    check((int) $advanced['rank_id'] === 2, 'TAC-Aufstieg fehlgeschlagen.');
    check($advanced['total_seconds'] === $saved['total_seconds'], 'Beförderung verbraucht Gesamtzeit.');
    check((int) $advanced['promotion_seconds'] === (int) $saved['promotion_seconds'] - 3600, 'Guthaben falsch verbraucht.');
    $pending2 = pending_promotions($advanced);
    check(count($pending2) === 1 && $pending2[0]['id'] !== $pending[0]['id'], 'Nächste volle Stunde fehlt.');
    duty_action($leader, $member, 'confirm', $pending2[0]['id']);
    check(pending_promotions($get($member)) === [], 'Restsekunden erzeugen Beförderung.');
    $audit = service_query('SELECT * FROM promotions WHERE id = ?', [$pending[0]['id']])->fetch();
    check((int) $audit['confirmed_by'] === $leader && $audit['confirmed_at'] !== null, 'Bestätiger nicht protokolliert.');
    service_query('UPDATE users SET promotion_seconds = 3600, active_by = ? WHERE id = ?', [$leader, $intel]);
    synchronize_user($intel);
    $intelPending = pending_promotions($get($intel));
    duty_action($leader, $intel, 'confirm', $intelPending[0]['id']);
    check((int) $get($intel)['rank_step'] === 1 && (int) $get($intel)['rank_id'] === 20, 'Intel 0/10 → 1/10 falsch.');
    service_query('UPDATE users SET rank_id = 49, rank_step = 0, promotion_seconds = 7200 WHERE id = ?', [$intel]);
    synchronize_user($intel);
    check(pending_promotions($get($intel)) === [], 'Elite hat automatischen Aufstieg.');
    echo "Alle EURO-Rang-, Zeit-, Berechtigungs- und Audit-Tests bestanden.\n";
} finally {
    foreach ($ids as $id) {
        service_query('DELETE FROM promotions WHERE user_id = ?', [$id]);
        service_query('DELETE FROM duty_log WHERE user_id = ?', [$id]);
    }
    foreach (array_reverse($ids) as $id) {
        service_query('DELETE FROM users WHERE id = ?', [$id]);
    }
}
