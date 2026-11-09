<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function page_header(string $title, ?array $user = null): void
{
    ?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · GreenPay</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/"><span class="logo">G</span> GreenPay</a>
    <?php if ($user): ?>
        <nav>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/transfer.php">Send money</a>
            <span class="who"><?= e($user['full_name']) ?></span>
            <a class="ghost" href="/logout.php">Sign out</a>
        </nav>
    <?php endif; ?>
</header>
<main class="<?= $user ? 'app' : 'auth' ?>">
<?php
}

function page_footer(): void
{
    ?>
</main>
<footer class="foot">GreenPay demo · training app with intentional bugs · never deploy it</footer>
</body>
</html>
<?php
}
