<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$schema = file_get_contents(BASE_PATH . '/database/migrations/schema.sql');

if ($schema === false) {
    fwrite(STDERR, "Schema file not found.\n");
    exit(1);
}

try {
    Database::pdo()->exec($schema);
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed. Check database credentials, privileges, and the SQL schema.\n");
    exit(1);
}
echo "Database schema migrated successfully.\n";
