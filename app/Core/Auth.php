<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    /** @var array<string, mixed>|null */
    private static ?array $user = null;

    public static function boot(): void
    {
        self::$user = null;
        if (!empty($_SESSION['user_id'])) {
            $user = User::find((int) $_SESSION['user_id']);
            if (!$user || (int) $user['is_active'] !== 1 ||
                !hash_equals(hash('sha256', (string) $user['password_hash']), (string) ($_SESSION['password_fingerprint'] ?? '')) ||
                time() - (int) ($_SESSION['last_activity'] ?? 0) > 3600) {
                unset($_SESSION['user_id'], $_SESSION['last_activity'], $_SESSION['password_fingerprint']);
                session_regenerate_id(true);
                return;
            }
            self::$user = $user;
            $_SESSION['last_activity'] = time();
        }
    }

    public static function attempt(string $email, string $password, string $ip): bool
    {
        if (User::tooManyAttempts($email, $ip)) {
            return false;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, (string) $user['password_hash'])) {
            User::recordLoginAttempt($email, $ip, false);
            return false;
        }

        if ((int) $user['is_active'] !== 1) {
            User::recordLoginAttempt($email, $ip, false);
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['password_fingerprint'] = hash('sha256', (string) $user['password_hash']);
        $_SESSION['last_activity'] = time();
        unset($_SESSION['_csrf_token']);
        self::$user = $user;
        User::recordLoginAttempt($email, $ip, true);
        User::touchLastLogin((int) $user['id']);
        activity('login', 'users', (int) $user['id'], 'Connexion administrateur');
        return true;
    }

    public static function logout(): void
    {
        if (self::$user) {
            activity('logout', 'users', (int) self::$user['id'], 'Déconnexion administrateur');
        }

        unset($_SESSION['user_id'], $_SESSION['last_activity'], $_SESSION['password_fingerprint'], $_SESSION['_csrf_token'], $_SESSION['_old']);
        session_regenerate_id(true);
        self::$user = null;
    }

    public static function check(): bool
    {
        return self::$user !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        return self::$user;
    }

    public static function id(): ?int
    {
        return self::$user ? (int) self::$user['id'] : null;
    }

    public static function hasRole(string $role): bool
    {
        return self::$user && self::$user['role'] === $role;
    }
}
