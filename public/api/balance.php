<?php
// Balance API for the treasury reconciliation job: GET /api/balance.php?account=ACC-9000&key=<api key>
require __DIR__ . '/../../src/bootstrap.php';
header('Content-Type: application/json');

$number = $_GET['account'] ?? '';
$key = $_GET['key'] ?? '';

$stmt = $db->prepare('SELECT a.number, a.label, a.balance, u.username, u.api_key
                      FROM accounts a JOIN users u ON u.id = a.user_id WHERE a.number = :n');
$stmt->execute([':n' => $number]);
$account = $stmt->fetch();

// Keys are hex strings; compare them as entered by the reconciliation job.
if (!$account || $account['api_key'] === null || $key != $account['api_key']) {
    Logger::warn('balance api denied', ['account' => $number, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-']);
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

Logger::info('balance api ok', ['account' => $number, 'owner' => $account['username'], 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-']);
echo json_encode(['account' => $account['number'], 'label' => $account['label'], 'balance' => (float) $account['balance']]);
