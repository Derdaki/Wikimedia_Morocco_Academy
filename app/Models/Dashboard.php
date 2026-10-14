<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Dashboard
{
    public static function metrics(): array
    {
        $pdo = Database::pdo();

        return [
            'events' => (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn(),
            'upcoming_events' => (int) $pdo->query('SELECT COUNT(*) FROM events WHERE status = "published" AND start_at >= NOW()')->fetchColumn(),
            'projects' => (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
            'news' => (int) $pdo->query('SELECT COUNT(*) FROM news WHERE status = "published"')->fetchColumn(),
            'team' => (int) $pdo->query('SELECT COUNT(*) FROM team_members WHERE is_visible = 1')->fetchColumn(),
            'unread_messages' => (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE read_at IS NULL')->fetchColumn(),
        ];
    }

    public static function latestActivity(): array
    {
        return Database::pdo()
            ->query('SELECT a.*, u.name AS user_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 8')
            ->fetchAll();
    }

    public static function unreadMessages(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM contact_messages WHERE read_at IS NULL ORDER BY created_at DESC LIMIT 5')
            ->fetchAll();
    }
}
