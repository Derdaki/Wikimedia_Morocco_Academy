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

    public static function toggle(int $id): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
        $admins = $pdo->query('SELECT id FROM users WHERE role = "super_admin" AND is_active = 1 FOR UPDATE')->fetchAll();
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
