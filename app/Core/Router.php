<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, action:array{0:class-string,1:string}}> */
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    /**
     * @param array{0:class-string,1:string} $action
     */
    private function add(string $method, string $path, array $action): void
    {
        $pattern = preg_quote($path, '#');
        $pattern = preg_replace('#\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}#', '(?P<$1>[^/]+)', $pattern);
        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . rtrim((string) $pattern, '/') . '/?$#',
            'action' => $action,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        if ($method === 'HEAD') $method = 'GET';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $allowed = [];
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path)) $allowed[] = $route['method'];
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn ($key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            [$class, $methodName] = $route['action'];
            $controller = new $class();
            $controller->{$methodName}(...array_values($params));
            return;
        }

        if ($allowed) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowed)));
            View::render('public/errors/request', ['title' => 'Méthode non autorisée', 'message' => 'Cette action nécessite une autre méthode HTTP.']);
            return;
        }
        http_response_code(404);
        View::render('public/errors/404', [
            'title' => 'Page introuvable',
        ], 'public');
    }
}
