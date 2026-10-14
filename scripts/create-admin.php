<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Models\User;

$name = trim((string) readline('Name: '));
$email = trim((string) readline('Email: '));
$password = (string) readline('Password: ');
$roleInput = trim((string) readline('Role [super_admin/editor] (default super_admin): '));
$role = $roleInput === 'editor' ? 'editor' : 'super_admin';

if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160 || strlen($password) < 10 || strlen($password) > 72) {
    fwrite(STDERR, "Provide a name, a valid email, and a password of 10 to 72 bytes.\n");
    exit(1);
}

try {
    $id = User::create($name, $email, $password, $role);
    echo "Administrator created with ID {$id}.\n";
} catch (Throwable $throwable) {
    fwrite(STDERR, "Could not create administrator. The email may already exist.\n");
    exit(1);
}
