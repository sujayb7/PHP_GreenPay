<?php
// Creates data/greenpay.sqlite with the schema and demo users. Safe to re-run: it starts from an empty file.
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
$path = $config['db_path'];
@mkdir(dirname($path), 0775, true);
if (is_file($path)) {
    unlink($path);
}

$db = new PDO('sqlite:' . $path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));

// Demo users. Passwords are printed by install.sh; they are demo values only.
$users = [
    ['admin',    'Grace Admin',     'admin',    'GreenAdmin!2026', null],
    ['alice',    'Alice Moreno',    'customer', 'alice-wallet-42',  null],
    ['bob',      'Bob Okafor',      'customer', 'bob-saves-77',     null],
    // The reconciliation job's key was issued by the old billing system (md5 of the job id).
    ['treasury', 'Treasury Bot',    'service',  bin2hex(random_bytes(12)), md5('240610708')],
];
$insert = $db->prepare('INSERT INTO users (username, full_name, role, password_hash, api_key) VALUES (?, ?, ?, ?, ?)');
foreach ($users as [$username, $name, $role, $password, $apiKey]) {
    $insert->execute([$username, $name, $role, password_hash($password, PASSWORD_DEFAULT), $apiKey]);
}

$accounts = [
    ['ACC-1001', 1, 'Operations float', 250000.00],
    ['ACC-2001', 2, 'Everyday',           3120.55],
    ['ACC-2002', 2, 'Savings',           15890.00],
    ['ACC-3001', 3, 'Everyday',           842.10],
    ['ACC-9000', 4, 'Treasury settlement', 1200000.00],
];
$insertAccount = $db->prepare('INSERT INTO accounts (number, user_id, label, balance) VALUES (?, ?, ?, ?)');
foreach ($accounts as $a) {
    $insertAccount->execute($a);
}

$tx = $db->prepare('INSERT INTO transactions (from_account, to_account, amount, memo, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)');
$tx->execute(['ACC-2001', 'ACC-3001', 45.00, 'Dinner split', 2, gmdate('Y-m-d\TH:i:s\Z', time() - 86400 * 2)]);
$tx->execute(['ACC-3001', 'ACC-2001', 12.50, 'Coffee', 3, gmdate('Y-m-d\TH:i:s\Z', time() - 86400)]);
$tx->execute(['ACC-9000', 'ACC-1001', 50000.00, 'Daily float top-up', 4, gmdate('Y-m-d\TH:i:s\Z', time() - 3600 * 5)]);

echo "Database ready: $path\n";
