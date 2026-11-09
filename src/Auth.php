<?php
declare(strict_types=1);

final class Auth
{
    /** Returns the user row when the username and password match an active user. */
    public static function attempt(PDO $db, string $username, string $password): ?array
    {
        $stmt = $db->prepare("SELECT id, username, full_name, role, password_hash FROM users WHERE username = :u AND status = 'active'");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    public static function user(PDO $db): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        $stmt = $db->prepare('SELECT id, username, full_name, role FROM users WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public static function requireUser(PDO $db): array
    {
        $user = self::user($db);
        if ($user === null) {
            header('Location: /login.php');
            exit;
        }
        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
