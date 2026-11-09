<?php
require __DIR__ . '/../src/bootstrap.php';
$user = Auth::requireUser($db);

$accounts = $db->prepare('SELECT number, label, balance FROM accounts WHERE user_id = :id ORDER BY number');
$accounts->execute([':id' => $user['id']]);
$accounts = $accounts->fetchAll();
$numbers = array_column($accounts, 'number');

$recent = [];
if ($numbers) {
    $in = implode(',', array_fill(0, count($numbers), '?'));
    $stmt = $db->prepare("SELECT from_account, to_account, amount, memo, created_at FROM transactions
                          WHERE from_account IN ($in) OR to_account IN ($in) ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([...$numbers, ...$numbers]);
    $recent = $stmt->fetchAll();
}

page_header('Dashboard', $user);
?>
<h1>Hello, <?= e(explode(' ', $user['full_name'])[0]) ?></h1>
<div class="grid">
    <?php foreach ($accounts as $a): ?>
        <section class="card account">
            <span class="muted"><?= e($a['label']) ?> · <?= e($a['number']) ?></span>
            <strong><?= money((float) $a['balance']) ?></strong>
        </section>
    <?php endforeach; ?>
</div>
<section class="card">
    <h2>Recent activity</h2>
    <?php if (!$recent): ?>
        <p class="muted">No transactions yet.</p>
    <?php else: ?>
        <table>
            <tr><th>When</th><th>From</th><th>To</th><th>Memo</th><th class="num">Amount</th></tr>
            <?php foreach ($recent as $t): ?>
                <tr>
                    <td><?= e($t['created_at']) ?></td><td><?= e($t['from_account']) ?></td><td><?= e($t['to_account']) ?></td>
                    <td><?= e($t['memo']) ?></td><td class="num"><?= money((float) $t['amount']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</section>
<?php page_footer();
