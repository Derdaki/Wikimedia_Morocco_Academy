<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
if (!str_ends_with((string) App\Core\Env::get('DB_DATABASE'), '_test')) exit(1);
if (($argv[1] ?? '') === 'remove') {
    $stmt = App\Core\Database::pdo()->prepare('DELETE FROM users WHERE email = ?');
    $stmt->execute([$argv[2] ?? '']);
    exit;
}
$email = 'browser-' . bin2hex(random_bytes(6)) . '@example.test';
$password = bin2hex(random_bytes(16));
App\Models\User::create('Browser verification', $email, $password, 'super_admin');
echo json_encode(['email' => $email, 'password' => $password]);
