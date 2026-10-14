<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => mb_strtolower(trim($email))]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function tooManyAttempts(string $email, string $ip): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (email = :email OR ip_address = :ip) AND successful = 0 AND created_at >= (NOW() - INTERVAL 15 MINUTE)'
        );
        $stmt->execute([
            'email' => mb_strtolower(trim($email)),
            'ip' => $ip,
        ]);

        return (int) $stmt->fetchColumn() >= 5;
    }

    public static function recordLoginAttempt(string $email, string $ip, bool $successful): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO login_attempts (email, ip_address, successful, created_at)
             VALUES (:email, :ip, :successful, NOW())'
        );
        $stmt->execute([
            'email' => mb_strtolower(trim($email)),
            'ip' => $ip,
            'successful' => $successful ? 1 : 0,
        ]);
    }

    public static function touchLastLogin(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function all(): array
    {
        return Database::pdo()
            ->query('SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY created_at DESC')
            ->fetchAll();
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160 ||
            !in_array($role, ['super_admin', 'editor'], true)) {
            throw new \InvalidArgumentException('Invalid user details.');
        }
        self::validatePassword($password);
        $stmt = Database::pdo()->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_active, created_at, updated_at)
             VALUES (:name, :email, :password_hash, :role, 1, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'email' => mb_strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public static function setPassword(int $id, string $password): void
    {
        self::validatePassword($password);
        if (!self::find($id)) throw new \LogicException('User not found.');
        $stmt = Database::pdo()->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
    }

    private static function validatePassword(string $password): void
    {
        if (strlen($password) < 10 || strlen($password) > 72 || str_contains($password, "\0")) {
            throw new \InvalidArgumentException('Password must contain 10 to 72 bytes and no null characters.');
        }
    }

    public static function changeRole(int $id, string $role): void
    {
        if (!in_array($role, ['super_admin', 'editor'], true)) throw new \InvalidArgumentException('Invalid role.');
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            // Lock active administrators in the same order as deactivation to prevent lockout.
            $admins = $pdo->query('SELECT id FROM users WHERE role = "super_admin" AND is_active = 1 ORDER BY id FOR UPDATE')->fetchAll();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            if (!$user) throw new \LogicException('User not found.');
            if ($user['role'] === 'super_admin' && (int) $user['is_active'] === 1 && $role !== 'super_admin' && count($admins) <= 1) {
                throw new \LogicException('Le dernier super administrateur doit rester actif.');
            }
            $stmt = $pdo->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['role' => $role, 'id' => $id]);
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public static function toggle(int $id): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
        $admins = $pdo->query('SELECT id FROM users WHERE role = "super_admin" AND is_active = 1 ORDER BY id FOR UPDATE')->fetchAll();
        $user = self::find($id);
        if ($user && $user['role'] === 'super_admin' && (int) $user['is_active'] === 1 && count($admins) <= 1) {
            throw new \LogicException('Le dernier super administrateur doit rester actif.');
        }
        $stmt = Database::pdo()->prepare('UPDATE users SET is_active = 1 - is_active, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
