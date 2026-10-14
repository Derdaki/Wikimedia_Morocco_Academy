<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->requireRole('super_admin');

        $this->view('admin/users/index', [
            'title' => 'Users',
            'users' => User::all(),
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireRole('super_admin');
        $this->verifyCsrf();

        $errors = Validator::validate($_POST, [
            'name' => 'required|max:120',
            'email' => 'required|email|max:160',
            'password' => 'required|max:72',
        ]);

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $this->redirect('/admin/users');
        }

        if (strlen((string) $_POST['password']) < 10) {
            flash('error', 'Le mot de passe doit contenir au moins 10 caractères.');
            $this->redirect('/admin/users');
        }

        $role = ($_POST['role'] ?? '') === 'super_admin' ? 'super_admin' : 'editor';

        try {
            $id = User::create((string) $_POST['name'], (string) $_POST['email'], (string) $_POST['password'], $role);
            activity('create', 'users', $id, 'Utilisateur créé');
            flash('success', 'Utilisateur créé.');
        } catch (\Throwable) {
            flash('error', 'Impossible de créer cet utilisateur. Vérifiez que l’email est unique.');
        }

        $this->redirect('/admin/users');
    }

    public function toggle(string $id): void
    {
        $this->requireRole('super_admin');
        $this->verifyCsrf();
        if ((int) $id === \App\Core\Auth::id()) {
            flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            $this->redirect('/admin/users');
        }
        try {
            User::toggle((int) $id);
        } catch (\LogicException $exception) {
            flash('error', locale() === 'ar' ? 'يجب إبقاء مسؤول رئيسي نشط واحد على الأقل.' : $exception->getMessage());
            $this->redirect('/admin/users');
        }
        activity('toggle', 'users', (int) $id, 'Statut utilisateur modifié');
        flash('success', 'Statut mis à jour.');
        $this->redirect('/admin/users');
    }
}
