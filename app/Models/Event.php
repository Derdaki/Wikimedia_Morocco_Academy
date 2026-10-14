<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Event
{
    public static function categories(): array
    {
        return Database::pdo()->query('SELECT * FROM event_categories ORDER BY name_fr')->fetchAll();
    }

    public static function upcoming(int $limit = 3): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT e.*, c.name_fr AS category_fr, c.name_ar AS category_ar
             FROM events e
             LEFT JOIN event_categories c ON c.id = e.category_id
             WHERE e.status = "published" AND e.start_at >= NOW()
             ORDER BY e.start_at ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function related(int $eventId, ?int $categoryId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT e.*, c.name_fr AS category_fr, c.name_ar AS category_ar
             FROM events e
             LEFT JOIN event_categories c ON c.id = e.category_id
             WHERE e.status = "published" AND e.id <> :id
             AND (:category_null IS NULL OR e.category_id = :category_id)
             ORDER BY e.start_at ASC
             LIMIT 3'
        );
        $stmt->execute(['id' => $eventId, 'category_null' => $categoryId, 'category_id' => $categoryId]);

        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT e.*, c.name_fr AS category_fr, c.name_ar AS category_ar
             FROM events e
             LEFT JOIN event_categories c ON c.id = e.category_id
             WHERE e.slug = :slug AND e.status IN ("published", "cancelled")
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug]);
        $event = $stmt->fetch();

        return $event ?: null;
    }

    public static function adminFind(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch();

        return $event ?: null;
    }

    public static function search(array $filters = [], int $page = 1, int $perPage = 9, bool $admin = false): array
    {
        $where = [];
        $params = [];

        if (!$admin) {
            $where[] = 'e.status = "published"';
        }

        if (!empty($filters['q'])) {
            $where[] = '(e.title_fr LIKE :q_fr OR e.title_ar LIKE :q_ar OR e.location LIKE :q_location)';
            foreach (['q_fr', 'q_ar', 'q_location'] as $key) $params[$key] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'e.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['location'])) {
            $where[] = 'e.location LIKE :location';
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['status']) && $admin) {
            $where[] = 'e.status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['period'] ?? '') === 'upcoming') {
            $where[] = 'e.start_at >= NOW()';
        } elseif (($filters['period'] ?? '') === 'past') {
            $where[] = 'e.start_at < NOW()';
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $perPage = max(1, min(100, $perPage));

        $countStmt = Database::pdo()->prepare("SELECT COUNT(*) FROM events e {$sqlWhere}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $page = max(1, min($page, max(1, (int) ceil($total / $perPage))));
        $offset = ($page - 1) * $perPage;
        $order = ($filters['period'] ?? '') === 'upcoming' ? 'ASC' : 'DESC';

        $stmt = Database::pdo()->prepare(
            "SELECT e.*, c.name_fr AS category_fr, c.name_ar AS category_ar
             FROM events e
             LEFT JOIN event_categories c ON c.id = e.category_id
             {$sqlWhere}
             ORDER BY e.start_at {$order}, e.id {$order}
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'page' => $page,
        ];
    }

    public static function save(array $data, ?int $id = null): int
    {
        $fields = [
            'title_fr', 'title_ar', 'slug', 'short_description_fr', 'short_description_ar',
            'description_fr', 'description_ar', 'category_id', 'cover_image', 'start_at',
            'end_at', 'location', 'organizer', 'registration_url', 'status'
        ];

        if ($id === null) {
            $columns = implode(', ', $fields) . ', created_at, updated_at';
            $placeholders = ':' . implode(', :', $fields) . ', NOW(), NOW()';
            $stmt = Database::pdo()->prepare("INSERT INTO events ({$columns}) VALUES ({$placeholders})");
        } else {
            $assignments = implode(', ', array_map(static fn ($field): string => "{$field} = :{$field}", $fields));
            $stmt = Database::pdo()->prepare("UPDATE events SET {$assignments}, updated_at = NOW() WHERE id = :id");
            $data['id'] = $id;
        }

        foreach ($fields as $field) {
            $data[$field] = $data[$field] ?? null;
        }

        $stmt->execute($data);

        return $id ?? (int) Database::pdo()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM events WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function archive(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE events SET status = "archived", updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
