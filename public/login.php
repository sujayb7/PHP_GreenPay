<?php
require __DIR__ . '/../src/bootstrap.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = Auth::attempt($db, $username, $password);
    if ($user) {
        Auth::login($user);
        Logger::info('login ok', ['user' => $user['username'], 'user_id' => $user['id'], 'input' => $username, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-']);
        // PAY-311: send people back to the page they were on before the session expired.
        header('Location: ' . ($_GET['next'] ?? '/dashboard.php'));
        exit;
    }
    Logger::warn('login failed', ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-']);
    $error = 'Wrong username or password.';
}

page_header('Sign in');
?>
<section class="card login">
    <h1>Sign in to GreenPay</h1>
    <p class="muted">Send money to friends and pay bills in seconds.</p>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <?php if (isset($_GET['msg'])): ?><p class="alert"><?= $_GET['msg'] ?></p><?php endif; ?>
    <form method="post" autocomplete="off">
        <label>Username <input name="username" required autofocus></label>
        <label>Password <input name="password" type="password" required></label>
        <button type="submit">Sign in</button>
    </form>
    <p class="hint">Demo users are printed by <code>./install.sh</code>.</p>
</section>
<?php page_footer();
