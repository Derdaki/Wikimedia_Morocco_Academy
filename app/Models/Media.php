<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

final class Media
{
    public static function registerUpload(array $upload): int
    {
        try {
        return self::create(['path' => $upload['path'], 'original_name' => $upload['original_name'],
            'mime_type' => $upload['mime'], 'size_bytes' => $upload['size'], 'alt_text' => '',
            'attribution' => '', 'license' => '', 'created_by' => \App\Core\Auth::id()]);
        } catch (\Throwable $exception) {
            @unlink(PUBLIC_PATH . $upload['path']);
            error_log((string) $exception, 4);
            throw new RuntimeException('Le média n’a pas pu être enregistré.');
        }
    }

    public static function all(string $q = ''): array
    {
        if ($q !== '') {
            $stmt = Database::pdo()->prepare(
                'SELECT * FROM media WHERE original_name LIKE :q_name OR alt_text LIKE :q_alt OR attribution LIKE :q_author ORDER BY created_at DESC'
            );
            $stmt->execute(['q_name' => '%' . $q . '%', 'q_alt' => '%' . $q . '%', 'q_author' => '%' . $q . '%']);
            return $stmt->fetchAll();
        }

        return Database::pdo()->query('SELECT * FROM media ORDER BY created_at DESC')->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO media (path, original_name, mime_type, size_bytes, alt_text, attribution, license, created_by, created_at, updated_at)
             VALUES (:path, :original_name, :mime_type, :size_bytes, :alt_text, :attribution, :license, :created_by, NOW(), NOW())'
        );
        $stmt->execute($data);

        return (int) Database::pdo()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM media WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function delete(int $id): void
    {
        $media = self::find($id);

        if (!$media) {
            return;
        }

        if (self::isInUse((string) $media['path'])) {
            throw new RuntimeException('Ce média est encore utilisé par un contenu ou un paramètre du site.');
        }

        if (!str_starts_with((string) $media['path'], 'http')) {
            $path = PUBLIC_PATH . '/' . ltrim((string) $media['path'], '/');
            $real = realpath($path);
            $root = realpath(UPLOAD_PATH);
            if ($real && $root && str_starts_with($real, $root . DIRECTORY_SEPARATOR) && is_file($real)) {
                if (!unlink($real)) throw new RuntimeException('Impossible de supprimer ce fichier.');
            }
        }

        $stmt = Database::pdo()->prepare('DELETE FROM media WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private static function isInUse(string $path): bool
    {
        $checks = [
            ['events', 'cover_image'],
            ['projects', 'cover_image'],
            ['news', 'featured_image'],
            ['team_members', 'photo'],
            ['resources', 'file_url'],
        ];

        foreach ($checks as [$table, $column]) {
            $stmt = Database::pdo()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :path");
            $stmt->execute(['path' => $path]);
            if ((int) $stmt->fetchColumn() > 0) {
                return true;
            }
        }

        foreach (Database::pdo()->query('SELECT setting_value FROM settings') as $setting) {
            if ($setting['setting_value'] === $path) return true;
            $value = json_decode((string) $setting['setting_value'], true);
            if (is_array($value) && in_array($path, $value, true)) return true;
        }
        return false;
    }
}
