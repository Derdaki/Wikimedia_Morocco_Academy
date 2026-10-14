<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], string $layout = 'public'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            flash('error', 'Veuillez vous connecter pour continuer.');
            $this->redirect('/admin/login');
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!Auth::hasRole($role)) {
            http_response_code(403);
            $this->view('admin/errors/403', ['title' => 'Accès refusé'], 'admin');
            exit;
        }
    }

    protected function verifyCsrf(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? '')) {
            http_response_code(403);
            $this->view('public/errors/request', ['title' => 'Session expirée',
                'message' => 'La session a expiré. Veuillez recharger la page et réessayer.']);
            exit;
        }
    }
}
