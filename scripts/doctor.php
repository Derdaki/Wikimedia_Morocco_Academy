<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$failed = 0;
$check = static function (bool $ok, string $label) use (&$failed): void {
    echo ($ok ? 'OK ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) $failed++;
};
foreach (['pdo_mysql', 'mbstring', 'fileinfo'] as $extension) $check(extension_loaded($extension), 'PHP extension: ' . $extension);
$check(is_file(BASE_PATH . '/.env') || getenv('DB_DATABASE') !== false, 'Environment configuration supplied');
foreach ([UPLOAD_PATH, BASE_PATH . '/storage/sessions'] as $directory) {
    $probe = $directory . '/.write-check-' . bin2hex(random_bytes(4));
    $ok = @file_put_contents($probe, 'test') !== false;
    if ($ok) unlink($probe);
    $check($ok, 'Writable directory: ' . $directory);
}
try {
    $pdo = App\Core\Database::pdo();
    $check(true, 'PDO database connection');
    foreach (['users','events','projects','news','team_members','media','resources','settings','contact_messages','activity_logs','login_attempts'] as $table) {
        $pdo->query("SELECT 1 FROM {$table} LIMIT 1");
    }
    $check(true, 'Required database tables');
} catch (Throwable $exception) { $check(false, 'Database/schema unavailable: configure .env, then run migrate.php'); }
$router = require BASE_PATH . '/routes/web.php';
$property = (new ReflectionClass($router))->getProperty('routes');
foreach ($property->getValue($router) as $route) {
    [$class, $method] = $route['action'];
    $check(is_callable([$class ? new $class() : null, $method]), $route['method'] . ' ' . $class . '::' . $method);
}
exit($failed ? 1 : 0);
