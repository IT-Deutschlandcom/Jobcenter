<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/promotion_service.php';
$ids = service_query('SELECT id FROM users WHERE active_since IS NOT NULL OR promotion_seconds >= ?', [PROMOTION_INTERVAL])->fetchAll(PDO::FETCH_COLUMN);
foreach ($ids as $id) {
    synchronize_user($id);
}
echo "EURO Dienstzeiten und Beförderungen aktualisiert.\n";
