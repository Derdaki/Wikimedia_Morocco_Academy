<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
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
            'role' => 'required|in:super_admin,editor',
        ]);

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $this->redirect('/admin/users');
        }

        if (strlen((string) $_POST['password']) < 10 || strlen((string) $_POST['password']) > 72 || str_contains((string) $_POST['password'], "\0")) {
            flash('error', 'Password must contain 10 to 72 bytes and no null characters.');
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

    public function updateRole(string $id): void
    {
        $this->requireRole('super_admin');
        $this->verifyCsrf();
        $errors = Validator::validate($_POST, ['role' => 'required|in:super_admin,editor']);
        if ($errors) {
            flash('error', 'Invalid role.');
            $this->redirect('/admin/users');
        }
        try {
            User::changeRole((int) $id, (string) $_POST['role']);
            activity('role', 'users', (int) $id, 'User role changed');
            flash('success', 'Role updated.');
        } catch (\LogicException $exception) {
            flash('error', $exception->getMessage());
        }
        $this->redirect('/admin/users');
    }

    public function account(): void
    {
        $this->requireAuth();
        $this->view('admin/users/account', ['title' => 'My account'], 'admin');
    }

    public function updatePassword(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $errors = Validator::validate($_POST, [
            'current_password' => 'required', 'new_password' => 'required', 'password_confirmation' => 'required',
        ]);
        $user = Auth::user();
        if ($errors || !password_verify((string) ($_POST['current_password'] ?? ''), (string) $user['password_hash'])) {
            flash('error', 'Current password is incorrect.');
            $this->redirect('/admin/account');
        }
        if ($_POST['new_password'] !== $_POST['password_confirmation']) {
            flash('error', 'Passwords do not match.');
            $this->redirect('/admin/account');
        }
        try {
            User::setPassword((int) $user['id'], (string) $_POST['new_password']);
        } catch (\InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());
            $this->redirect('/admin/account');
        }
        activity('password', 'users', (int) $user['id'], 'Password changed');
        Auth::logout();
        flash('success', 'Password changed. Sign in again.');
        $this->redirect('/admin/login');
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
