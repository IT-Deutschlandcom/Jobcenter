<?php
require_once __DIR__ . '/payroll_service.php';
$user = current_user();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Bitte erneut anmelden.']);
    exit;
}
try {
    synchronize_visible_users($user);
    $user = current_user();
    $items = [];
    foreach (pending_promotions($user) as $promotion) {
        $credit = service_query('SELECT promotion_seconds FROM users WHERE id = ?', [$promotion['user_id']])->fetchColumn();
        $items[] = ['id' => (int) $promotion['id'], 'user_id' => (int) $promotion['user_id'],
            'can_confirm' => can_manage($user) && $promotion['user_id'] != $user['id'], 'username' => $promotion['username'],
            'due_hours' => (int) floor($credit / PROMOTION_INTERVAL),
            'rank' => rank_label(['rank_id' => $promotion['to_rank'], 'rank_step' => $promotion['to_step']])];
    }
    echo json_encode(['promotions' => $items, 'can_manage' => can_manage($user),
        'work' => work_summary($user, time())]);
} catch (Throwable $error) {
    error_log('EURO promotion polling failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Beförderungsstatus derzeit nicht verfügbar.']);
}
