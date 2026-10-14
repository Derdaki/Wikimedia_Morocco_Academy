<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$seed = file_get_contents(BASE_PATH . '/database/seeds/demo.sql');

if ($seed === false) {
    fwrite(STDERR, "Seed file not found.\n");
    exit(1);
}

try {
    Database::pdo()->beginTransaction();
    Database::pdo()->exec($seed);
    Database::pdo()->commit();
} catch (Throwable $exception) {
    if (Database::pdo()->inTransaction()) Database::pdo()->rollBack();
    fwrite(STDERR, "Seed failed. Run the migration first and check database configuration.\n");
    exit(1);
}
echo "Demonstration data inserted. Review and replace demo records before production use.\n";
