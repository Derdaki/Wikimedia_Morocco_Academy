<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\UploadService;
use App\Models\Media;
use RuntimeException;

final class MediaController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('admin/media/index', [
            'title' => 'Media Library',
            'media' => Media::all(trim((string) ($_GET['q'] ?? ''))),
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $errors = \App\Core\Validator::validate($_POST, ['alt_text' => 'max:255', 'attribution' => 'max:255', 'license' => 'max:120']);
        if ($errors) {
            flash('error', implode(' ', array_unique($errors)));
            $this->redirect('/admin/media');
        }

        try {
            $upload = UploadService::store($_FILES['file'] ?? [], 'media');
            $id = Media::create([
                'path' => $upload['path'],
                'original_name' => $upload['original_name'],
                'mime_type' => $upload['mime'],
                'size_bytes' => $upload['size'],
                'alt_text' => trim((string) ($_POST['alt_text'] ?? '')),
                'attribution' => trim((string) ($_POST['attribution'] ?? '')),
                'license' => trim((string) ($_POST['license'] ?? '')),
                'created_by' => Auth::id(),
            ]);
            activity('upload', 'media', $id, 'Média téléversé');
            flash('success', 'Média ajouté.');
        } catch (\PDOException $exception) {
            if (isset($upload)) @unlink(PUBLIC_PATH . $upload['path']);
            error_log((string) $exception, 4);
            flash('error', 'Le média n’a pas pu être enregistré.');
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
        }

        $this->redirect('/admin/media');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            Media::delete((int) $id);
            activity('delete', 'media', (int) $id, 'Média supprimé');
            flash('success', 'Média supprimé.');
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
        }
        $this->redirect('/admin/media');
    }
}
