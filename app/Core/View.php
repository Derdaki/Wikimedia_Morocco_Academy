<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], string $layout = 'public'): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = APP_PATH . '/Views/' . $view . '.php';
        $layoutPath = APP_PATH . '/Views/layouts/' . $layout . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View not found.';
            return;
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (!is_file($layoutPath)) {
            echo $content;
            return;
        }

        require $layoutPath;
    }
}
