<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\UploadService;
use App\Core\Validator;
use App\Models\Content;
use RuntimeException;

final class ContentController extends Controller
{
    public function index(string $type): void
    {
        $this->requireAuth();
        $config = $this->configOr404($type);

        $this->view('admin/content/index', [
            'title' => $config['label'],
            'type' => $type,
            'config' => $config,
            'result' => Content::adminList($type, ['q' => trim((string) ($_GET['q'] ?? ''))], max(1, (int) ($_GET['page'] ?? 1))),
        ], 'admin');
    }

    public function create(string $type): void
    {
        $this->requireAuth();
        $config = $this->configOr404($type);

        $this->view('admin/content/form', [
            'title' => ui('Create') . ' ' . ui($config['singular']),
            'type' => $type,
            'config' => $config,
            'item' => null,
            'categories' => Content::categories($type),
        ], 'admin');
    }

    public function store(string $type): void
    {
        $this->save($type);
    }

    public function edit(string $type, string $id): void
    {
        $this->requireAuth();
        $config = $this->configOr404($type);
        $item = Content::adminFind($type, (int) $id);

        if (!$item) {
            flash('error', 'Contenu introuvable.');
            $this->redirect('/admin/' . $type);
        }

        $this->view('admin/content/form', [
            'title' => (locale() === 'ar' ? 'تعديل ' : 'Modifier ') . ui($config['singular']),
            'type' => $type,
            'config' => $config,
            'item' => $item,
            'categories' => Content::categories($type),
        ], 'admin');
    }

    public function update(string $type, string $id): void
    {
        $this->save($type, (int) $id);
    }

    public function delete(string $type, string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $this->configOr404($type);
        Content::delete($type, (int) $id);
        activity('delete', $type, (int) $id, 'Contenu supprimé');
        flash('success', 'Contenu supprimé.');
        $this->redirect('/admin/' . $type);
    }

    private function save(string $type, ?int $id = null): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $config = $this->configOr404($type);
        remember_old($_POST + ['is_visible' => 0, 'is_featured' => 0]);

        $rules = $type === 'team'
            ? ['name' => 'required|max:160', 'role_fr' => 'required|max:160', 'slug' => 'required|max:190']
            : ['title_fr' => 'required|max:180', 'slug' => 'required|max:190'];

        $errors = Validator::validate($_POST, $rules);
        $errors += Validator::validate($_POST, [
            'title_ar' => 'max:180', 'role_ar' => 'max:160', 'author' => 'max:160',
            'image_path' => 'asset|max:500', 'file_url' => 'asset|max:500',
            'sort_order' => 'integer', 'category_id' => 'integer', 'published_at' => 'date',
            'status' => 'in:draft,published', 'project_status' => 'in:active,completed',
            'resource_type' => 'in:link,pdf,guide,document',
        ]);
        if (!empty($_POST['category_id']) && !in_array((int) $_POST['category_id'], array_column(Content::categories($type), 'id'))) $errors['category_id'] = 'Catégorie invalide.';
        if ($type !== 'team' && empty($_POST['status'])) $errors['status'] = 'Choix invalide.';
        if ($id !== null && !Content::adminFind($type, $id)) { http_response_code(404); return; }

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $this->redirect($id ? "/admin/{$type}/{$id}/edit" : "/admin/{$type}/create");
        }

        $image = trim((string) ($_POST['image_path'] ?? ''));

        try {
            if (!empty($_FILES['image_upload']['name'])) {
                $upload = UploadService::store($_FILES['image_upload'], $type, $type !== 'resources');
                \App\Models\Media::registerUpload($upload);
                $image = $upload['path'];
            }
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            $this->redirect($id ? "/admin/{$type}/{$id}/edit" : "/admin/{$type}/create");
        }

        $data = $this->payload($type, $image);

        try {
            $contentId = Content::save($type, $data, $id);
            activity($id ? 'update' : 'create', $type, $contentId, $config['singular'] . ' enregistré');
        } catch (\Throwable) {
            flash('error', 'Impossible d’enregistrer ce contenu. Vérifiez que le slug est unique.');
            $this->redirect($id ? "/admin/{$type}/{$id}/edit" : "/admin/{$type}/create");
        }

        clear_old();
        flash('success', 'Contenu enregistré.');
        $this->redirect('/admin/' . $type);
    }

    private function payload(string $type, string $image): array
    {
        if ($type === 'projects') {
            return [
                'title_fr' => trim((string) $_POST['title_fr']),
                'title_ar' => trim((string) ($_POST['title_ar'] ?? '')),
                'slug' => slugify((string) $_POST['slug']),
                'category_id' => ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null,
                'cover_image' => $image,
                'excerpt_fr' => trim((string) ($_POST['excerpt_fr'] ?? '')),
                'excerpt_ar' => trim((string) ($_POST['excerpt_ar'] ?? '')),
                'content_fr' => trim((string) ($_POST['content_fr'] ?? '')),
                'content_ar' => trim((string) ($_POST['content_ar'] ?? '')),
                'project_status' => $_POST['project_status'] ?? 'active',
                'status' => $_POST['status'] ?? 'draft',
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ];
        }

        if ($type === 'news') {
            return [
                'title_fr' => trim((string) $_POST['title_fr']),
                'title_ar' => trim((string) ($_POST['title_ar'] ?? '')),
                'slug' => slugify((string) $_POST['slug']),
                'category_id' => ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null,
                'featured_image' => $image,
                'excerpt_fr' => trim((string) ($_POST['excerpt_fr'] ?? '')),
                'excerpt_ar' => trim((string) ($_POST['excerpt_ar'] ?? '')),
                'content_fr' => trim((string) ($_POST['content_fr'] ?? '')),
                'content_ar' => trim((string) ($_POST['content_ar'] ?? '')),
                'author' => trim((string) ($_POST['author'] ?? 'Wikimedia Maroc')),
                'status' => $_POST['status'] ?? 'draft',
                'published_at' => !empty($_POST['published_at']) ? str_replace('T', ' ', (string) $_POST['published_at']) : date('Y-m-d H:i:s'),
            ];
        }

        if ($type === 'team') {
            return [
                'name' => trim((string) $_POST['name']),
                'slug' => slugify((string) $_POST['slug']),
                'role_fr' => trim((string) $_POST['role_fr']),
                'role_ar' => trim((string) ($_POST['role_ar'] ?? '')),
                'bio_fr' => trim((string) ($_POST['bio_fr'] ?? '')),
                'bio_ar' => trim((string) ($_POST['bio_ar'] ?? '')),
                'photo' => $image,
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
            ];
        }

        $fileUrl = trim((string) ($_POST['file_url'] ?? ''));

        return [
            'title_fr' => trim((string) $_POST['title_fr']),
            'title_ar' => trim((string) ($_POST['title_ar'] ?? '')),
            'slug' => slugify((string) $_POST['slug']),
            'description_fr' => trim((string) ($_POST['description_fr'] ?? '')),
            'description_ar' => trim((string) ($_POST['description_ar'] ?? '')),
            'resource_type' => $_POST['resource_type'] ?? 'link',
            'file_url' => $fileUrl !== '' ? $fileUrl : $image,
            'status' => $_POST['status'] ?? 'draft',
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];
    }

    private function configOr404(string $type): array
    {
        try {
            return Content::config($type);
        } catch (\InvalidArgumentException) {
            http_response_code(404);
            $this->view('public/errors/404', ['title' => 'Section introuvable'], 'admin');
            exit;
        }
    }
}
