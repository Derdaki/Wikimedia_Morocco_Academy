<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\UploadService;
use App\Core\Validator;
use App\Models\Event;
use RuntimeException;

final class EventController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => $_GET['status'] ?? '',
            'period' => $_GET['period'] ?? '',
        ];

        $this->view('admin/events/index', [
            'title' => 'Events',
            'result' => Event::search($filters, $page, 12, true),
            'upcoming' => Event::upcoming(4),
            'filters' => $filters,
        ], 'admin');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('admin/events/form', [
            'title' => 'Create Event',
            'event' => null,
            'categories' => Event::categories(),
        ], 'admin');
    }

    public function store(): void
    {
        $this->save();
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $event = Event::adminFind((int) $id);

        if (!$event) {
            flash('error', 'Événement introuvable.');
            $this->redirect('/admin/events');
        }

        $this->view('admin/events/form', [
            'title' => 'Edit Event',
            'event' => $event,
            'categories' => Event::categories(),
        ], 'admin');
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function archive(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        Event::archive((int) $id);
        activity('archive', 'events', (int) $id, 'Événement archivé');
        flash('success', 'Événement archivé.');
        $this->redirect('/admin/events');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        Event::delete((int) $id);
        activity('delete', 'events', (int) $id, 'Événement supprimé');
        flash('success', 'Événement supprimé.');
        $this->redirect('/admin/events');
    }

    private function save(?int $id = null): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        remember_old($_POST);

        $errors = Validator::validate($_POST, [
            'title_fr' => 'required|max:180',
            'slug' => 'required|max:190',
            'short_description_fr' => 'required|max:500',
            'description_fr' => 'required',
            'title_ar' => 'max:180',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'location' => 'required|max:180',
            'organizer' => 'required|max:180',
            'registration_url' => 'url|max:255',
            'cover_image' => 'asset|max:500',
            'category_id' => 'integer',
            'status' => 'required|in:draft,published,archived,cancelled',
        ]);

        if (!isset($errors['start_at'], $errors['end_at']) &&
            strtotime((string) ($_POST['end_at'] ?? '')) < strtotime((string) ($_POST['start_at'] ?? ''))) {
            $errors['end_at'] = 'La fin doit suivre le début.';
        }
        if (!empty($_POST['category_id']) && !in_array((int) $_POST['category_id'], array_column(Event::categories(), 'id'))) $errors['category_id'] = 'Catégorie invalide.';
        if ($id !== null && !Event::adminFind($id)) { http_response_code(404); return; }

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $this->redirect($id ? "/admin/events/{$id}/edit" : '/admin/events/create');
        }

        $cover = trim((string) ($_POST['cover_image'] ?? ''));

        try {
            if (!empty($_FILES['cover_upload']['name'])) {
                $upload = UploadService::store($_FILES['cover_upload'], 'events', true);
                \App\Models\Media::registerUpload($upload);
                $cover = $upload['path'];
            }
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            $this->redirect($id ? "/admin/events/{$id}/edit" : '/admin/events/create');
        }

        $data = [
            'title_fr' => trim((string) $_POST['title_fr']),
            'title_ar' => trim((string) ($_POST['title_ar'] ?? '')),
            'slug' => slugify((string) $_POST['slug']),
            'short_description_fr' => trim((string) $_POST['short_description_fr']),
            'short_description_ar' => trim((string) ($_POST['short_description_ar'] ?? '')),
            'description_fr' => trim((string) $_POST['description_fr']),
            'description_ar' => trim((string) ($_POST['description_ar'] ?? '')),
            'category_id' => ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null,
            'cover_image' => $cover,
            'start_at' => str_replace('T', ' ', (string) $_POST['start_at']),
            'end_at' => str_replace('T', ' ', (string) $_POST['end_at']),
            'location' => trim((string) $_POST['location']),
            'organizer' => trim((string) $_POST['organizer']),
            'registration_url' => trim((string) ($_POST['registration_url'] ?? '')),
            'status' => in_array($_POST['status'] ?? '', ['draft', 'published', 'archived', 'cancelled'], true) ? $_POST['status'] : 'draft',
        ];

        Database::pdo()->beginTransaction();
        try {
            $eventId = Event::save($data, $id);
            activity($id ? 'update' : 'create', 'events', $eventId, $id ? 'Événement modifié' : 'Événement créé');
            Database::pdo()->commit();
        } catch (\Throwable $throwable) {
            Database::pdo()->rollBack();
            flash('error', 'Impossible d’enregistrer cet événement. Vérifiez que le slug est unique.');
            $this->redirect($id ? "/admin/events/{$id}/edit" : '/admin/events/create');
        }

        clear_old();
        flash('success', 'Événement enregistré.');
        $this->redirect('/admin/events');
    }
}
