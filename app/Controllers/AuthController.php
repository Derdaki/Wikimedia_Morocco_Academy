<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('/admin');
        }

        $this->view('admin/login', ['title' => 'Connexion'], 'auth');
    }

    public function authenticate(): void
    {
        $this->verifyCsrf();

        $email = is_string($_POST['email'] ?? null) ? substr($_POST['email'], 0, 160) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        if (Auth::attempt($email, $password, $ip)) {
            flash('success', 'Bienvenue dans le tableau de bord.');
            $this->redirect('/admin');
        }

        flash('error', 'Identifiants invalides ou trop de tentatives.');
        $this->redirect('/admin/login');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Auth::logout();
        flash('success', 'Vous êtes déconnecté.');
        $this->redirect('/admin/login');
    }
}
