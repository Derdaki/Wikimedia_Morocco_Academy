<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Console;
use App\Models\User;

Console::windowsPrompt('reset');
$email = trim(Console::read('Existing account email: '));
$password = Console::password();
try {
    $user = User::findByEmail($email);
    if (!$user) throw new LogicException('User not found.');
    User::setPassword((int) $user['id'], $password);
    echo "Password reset. Existing sessions are revoked on their next request. Account status and role are unchanged.\n";
} catch (InvalidArgumentException | LogicException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable) {
    fwrite(STDERR, "Password reset failed. Check the local database configuration.\n");
    exit(1);
}
