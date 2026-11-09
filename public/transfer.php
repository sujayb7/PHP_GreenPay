<?php
require __DIR__ . '/../src/bootstrap.php';
$user = Auth::requireUser($db);

$own = $db->prepare('SELECT number, label, balance FROM accounts WHERE user_id = :id ORDER BY number');
$own->execute([':id' => $user['id']]);
$own = $own->fetchAll();

$message = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = $_POST['from_account'] ?? '';
    $to = trim($_POST['to_account'] ?? '');
    $amount = round((float) ($_POST['amount'] ?? 0), 2);
    $memo = trim($_POST['memo'] ?? '');

    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } elseif (!in_array($from, array_column($own, 'number'), true)) {
        $error = 'Choose one of your accounts.';
    } elseif ($amount <= 0) {
        $error = 'Enter an amount.';
    } else {
        $db->beginTransaction();
        $debit = $db->prepare('UPDATE accounts SET balance = balance - :a WHERE number = :n AND balance >= :a');
        $debit->execute([':a' => $amount, ':n' => $from]);
        $credit = $db->prepare('UPDATE accounts SET balance = balance + :a WHERE number = :n');
        $credit->execute([':a' => $amount, ':n' => $to]);
        if ($debit->rowCount() !== 1 || $credit->rowCount() !== 1) {
            $db->rollBack();
            $error = 'Transfer failed: check the recipient account and your balance.';
            Logger::warn('transfer rejected', ['from' => $from, 'to' => $to, 'amount' => $amount, 'user_id' => $user['id']]);
        } else {
            $db->prepare('INSERT INTO transactions (from_account, to_account, amount, memo, created_by) VALUES (?, ?, ?, ?, ?)')
               ->execute([$from, $to, $amount, $memo, $user['id']]);
            $db->commit();
            Logger::info('transfer ok', ['from' => $from, 'to' => $to, 'amount' => $amount, 'user_id' => $user['id'], 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-']);
            $message = 'Sent ' . money($amount) . ' to ' . $to . '.';
        }
    }
}

page_header('Send money', $user);
?>
<section class="card narrow">
    <h1>Send money</h1>
    <?php if ($message): ?><p class="ok"><?= e($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf'] ?? '') ?>">
        <label>From
            <select name="from_account">
                <?php foreach ($own as $a): ?>
                    <option value="<?= e($a['number']) ?>"><?= e($a['label']) ?> (<?= e($a['number']) ?>) · <?= money((float) $a['balance']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>To account <input name="to_account" placeholder="ACC-3001" required></label>
        <label>Amount <input name="amount" type="number" step="0.01" min="0.01" required></label>
        <label>Memo <input name="memo" maxlength="80"></label>
        <button type="submit">Send</button>
    </form>
</section>
<?php page_footer();
