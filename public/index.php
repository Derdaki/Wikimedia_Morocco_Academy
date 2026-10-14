<?php

declare(strict_types=1);

ini_set('display_errors', '0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' https: blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'self'");
header('Cache-Control: no-store');
ob_start();
try {
    require dirname(__DIR__) . '/app/bootstrap.php';
    foreach (array_merge($_GET, $_POST) as $value) {
        if (!is_string($value)) {
            http_response_code(400);
            echo 'Bad request';
            ob_end_flush();
            exit;
        }
    }
    $router = require BASE_PATH . '/routes/web.php';
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    ob_end_flush();
} catch (Throwable $exception) {
    ob_end_clean();
    error_log((string) $exception, 4);
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Service indisponible</title><h1>Service temporairement indisponible</h1><p>Veuillez réessayer plus tard.</p></html>';
}
