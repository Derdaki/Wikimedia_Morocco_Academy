<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (str_starts_with($class, $prefix) === false) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = APP_PATH . '/' . $relative . '.php';

    if (is_file($path)) {
        require $path;
    }
});

require APP_PATH . '/Helpers/functions.php';

Env::load(BASE_PATH . '/.env');
date_default_timezone_set((string) Env::get('APP_TIMEZONE', 'Africa/Casablanca'));
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

$sessionName = Env::get('SESSION_NAME', 'wmma_session');
$sessionPath = (string) Env::get('SESSION_PATH', BASE_PATH . '/storage/sessions');

if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
}

$probeFile = $sessionPath . DIRECTORY_SEPARATOR . '.session-probe';

if (@file_put_contents($probeFile, '1') === false) {
    $sessionPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wikimedia-maroc-sessions';

    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0700, true);
    }
} else {
    @unlink($probeFile);
}

session_save_path($sessionPath);
session_name($sessionName);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!session_start()) throw new RuntimeException('Session storage is not writable.');
}

Auth::boot();
