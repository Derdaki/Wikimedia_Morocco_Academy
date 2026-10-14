<?php
declare(strict_types=1);

$public = realpath(dirname(__DIR__) . '/public');
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (str_contains($path, "\0")) { http_response_code(400); exit('Bad request'); }
$file = realpath($public . $path);
if ($file && str_starts_with($file, $public . DIRECTORY_SEPARATOR) && is_file($file) &&
    preg_match('/\.(css|js|jpg|jpeg|png|gif|webp|svg|ico|pdf|txt)$/i', $file) &&
    !str_contains($path, '/.')) {
    if (str_starts_with($path, '/uploads/')) {
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: default-src 'none'; sandbox");
        if (str_ends_with(strtolower($path), '.pdf')) header('Content-Disposition: attachment');
    }
    return false;
}
require $public . '/index.php';
