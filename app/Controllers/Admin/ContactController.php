<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

final class ContactController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $messages = Database::pdo()
            ->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 100')
            ->fetchAll();

        $this->view('admin/contacts/index', [
            'title' => 'Contact Messages',
            'messages' => $messages,
        ], 'admin');
    }

    public function markRead(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $stmt = Database::pdo()->prepare('UPDATE contact_messages SET read_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => (int) $id]);
        flash('success', 'Message marqué comme lu.');
        $this->redirect('/admin/contact-messages');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $stmt = Database::pdo()->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->execute(['id' => (int) $id]);
        flash('success', 'Message supprimé.');
        $this->redirect('/admin/contact-messages');
    }
}
