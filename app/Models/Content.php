<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use InvalidArgumentException;

final class Content
{
    public const TYPES = [
        'projects' => [
            'table' => 'projects',
            'category_table' => 'project_categories',
            'label' => 'Projets',
            'singular' => 'Projet',
            'public_path' => '/nos-projets',
        ],
        'news' => [
            'table' => 'news',
            'category_table' => 'news_categories',
            'label' => 'Actualités',
            'singular' => 'Article',
            'public_path' => '/actualites',
        ],
        'team' => [
            'table' => 'team_members',
            'category_table' => null,
            'label' => 'Équipe',
            'singular' => 'Membre',
            'public_path' => '/notre-equipe',
        ],
        'resources' => [
            'table' => 'resources',
            'category_table' => null,
            'label' => 'Ressources',
            'singular' => 'Ressource',
            'public_path' => '/ressources',
        ],
    ];

    public static function config(string $type): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new InvalidArgumentException('Unknown content type.');
        }

        return self::TYPES[$type];
    }

    public static function categories(string $type): array
    {
        $config = self::config($type);

        if (!$config['category_table']) {
            return [];
        }

        return Database::pdo()->query("SELECT * FROM {$config['category_table']} ORDER BY name_fr")->fetchAll();
    }

    public static function latestNews(int $limit = 3): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT n.*, c.name_fr AS category_fr, c.name_ar AS category_ar
             FROM news n
             LEFT JOIN news_categories c ON c.id = n.category_id
             WHERE n.status = "published" AND n.published_at <= NOW()
             ORDER BY n.published_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function featuredProjects(int $limit = 3): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.*, c.name_fr AS category_fr, c.name_ar AS category_ar
             FROM projects p
             LEFT JOIN project_categories c ON c.id = p.category_id
             WHERE p.status = "published" AND p.is_featured = 1
             ORDER BY p.sort_order ASC, p.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function published(string $type, array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $config = self::config($type);
        $table = $config['table'];
        $where = [];
        $params = [];

        if ($type === 'team') {
            $where[] = 'is_visible = 1';
        } else {
            $where[] = $table . '.status = "published"';
            if ($type === 'news') $where[] = 'news.published_at <= NOW()';
        }

        if (!empty($filters['q'])) {
            if ($type === 'team') {
                $where[] = '(name LIKE :q_fr OR role_fr LIKE :q_ar OR role_ar LIKE :q_extra)';
            } else {
                $where[] = '(title_fr LIKE :q_fr OR title_ar LIKE :q_ar)';
            }
            foreach (['q_fr', 'q_ar'] as $key) $params[$key] = '%' . $filters['q'] . '%';
            if (str_contains(end($where), ':q_extra')) $params['q_extra'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['category_id']) && in_array($type, ['projects', 'news'], true)) {
            $where[] = $table . '.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);
        $perPage = max(1, min(100, $perPage));
        $orderBy = match ($type) {
            'news' => 'news.published_at DESC, news.id DESC',
            'team', 'resources', 'projects' => $table . '.sort_order ASC, ' . $table . '.id DESC',
            default => 'created_at DESC',
        };

        $countStmt = Database::pdo()->prepare("SELECT COUNT(*) FROM {$table} {$sqlWhere}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $page = max(1, min($page, max(1, (int) ceil($total / $perPage))));
        $offset = ($page - 1) * $perPage;

        $join = '';
        $select = "{$table}.*";
        if ($type === 'projects') {
            $join = 'LEFT JOIN project_categories c ON c.id = projects.category_id';
            $select .= ', c.name_fr AS category_fr, c.name_ar AS category_ar';
        } elseif ($type === 'news') {
            $join = 'LEFT JOIN news_categories c ON c.id = news.category_id';
            $select .= ', c.name_fr AS category_fr, c.name_ar AS category_ar';
        }

        $stmt = Database::pdo()->prepare(
            "SELECT {$select} FROM {$table} {$join} {$sqlWhere} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset"
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

    public static function findPublishedBySlug(string $type, string $slug): ?array
    {
        $config = self::config($type);
        $table = $config['table'];
        $statusSql = $type === 'team' ? 'is_visible = 1' : 'status = "published"';
        if ($type === 'news') $statusSql .= ' AND published_at <= NOW()';
        $stmt = Database::pdo()->prepare("SELECT * FROM {$table} WHERE slug = :slug AND {$statusSql} LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function adminList(string $type, array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $config = self::config($type);
        $table = $config['table'];
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            if ($type === 'team') {
                $where[] = '(name LIKE :q_fr OR role_fr LIKE :q_ar)';
            } else {
                $where[] = '(title_fr LIKE :q_fr OR title_ar LIKE :q_ar OR slug LIKE :q_extra)';
            }
            foreach (['q_fr', 'q_ar'] as $key) $params[$key] = '%' . $filters['q'] . '%';
            if (str_contains(end($where), ':q_extra')) $params['q_extra'] = '%' . $filters['q'] . '%';
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $order = $type === 'news' ? 'published_at DESC, created_at DESC' : 'sort_order ASC, created_at DESC';
        $count = Database::pdo()->prepare("SELECT COUNT(*) FROM {$table} {$sqlWhere}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $perPage = max(1, min(100, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $stmt = Database::pdo()->prepare("SELECT * FROM {$table} {$sqlWhere} ORDER BY {$order}, id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    public static function adminFind(string $type, int $id): ?array
    {
        $config = self::config($type);
        $table = $config['table'];
        $stmt = Database::pdo()->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function save(string $type, array $data, ?int $id = null): int
    {
        $config = self::config($type);
        $table = $config['table'];
        $fields = self::fields($type);

        foreach ($fields as $field) {
            $data[$field] = $data[$field] ?? null;
        }

        if ($id === null) {
            $columns = implode(', ', $fields) . ', created_at, updated_at';
            $placeholders = ':' . implode(', :', $fields) . ', NOW(), NOW()';
            $stmt = Database::pdo()->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
        } else {
            $assignments = implode(', ', array_map(static fn ($field): string => "{$field} = :{$field}", $fields));
            $stmt = Database::pdo()->prepare("UPDATE {$table} SET {$assignments}, updated_at = NOW() WHERE id = :id");
            $data['id'] = $id;
        }

        $stmt->execute($data);

        return $id ?? (int) Database::pdo()->lastInsertId();
    }

    public static function delete(string $type, int $id): void
    {
        $config = self::config($type);
        $stmt = Database::pdo()->prepare("DELETE FROM {$config['table']} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private static function fields(string $type): array
    {
        return match ($type) {
            'projects' => [
                'title_fr', 'title_ar', 'slug', 'category_id', 'cover_image',
                'excerpt_fr', 'excerpt_ar', 'content_fr', 'content_ar',
                'project_status', 'status', 'is_featured', 'sort_order'
            ],
            'news' => [
                'title_fr', 'title_ar', 'slug', 'category_id', 'featured_image',
                'excerpt_fr', 'excerpt_ar', 'content_fr', 'content_ar',
                'author', 'status', 'published_at'
            ],
            'team' => [
                'name', 'slug', 'role_fr', 'role_ar', 'bio_fr', 'bio_ar',
                'photo', 'sort_order', 'is_visible'
            ],
            'resources' => [
                'title_fr', 'title_ar', 'slug', 'description_fr', 'description_ar',
                'resource_type', 'file_url', 'status', 'sort_order'
            ],
            default => throw new InvalidArgumentException('Unknown content type.'),
        };
    }
}
