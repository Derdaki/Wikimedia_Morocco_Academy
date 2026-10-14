<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    public static function all(): array
    {
        $rows = Database::pdo()->query('SELECT setting_key, setting_value FROM settings ORDER BY setting_key')->fetchAll();
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public static function updateMany(array $settings): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_at)
             VALUES (:setting_key, :setting_value, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        );

        Database::pdo()->beginTransaction();
        try {
        foreach ($settings as $key => $value) {
            $stmt->execute([
                'setting_key' => $key,
                'setting_value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value,
            ]);
        }
        Database::pdo()->commit();
        } catch (\Throwable $exception) {
            Database::pdo()->rollBack();
            throw $exception;
        }
    }
}
