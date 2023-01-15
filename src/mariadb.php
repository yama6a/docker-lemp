<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'sql_results.php';

use Doctrine\DBAL\Connection;

$connectionParams = [
    'dbname'   => $_ENV['MARIADB_DATABASE'] ?? null,
    'password' => $_ENV['MARIADB_PASSWORD'] ?? null,
    'user'     => $_ENV['MARIADB_USER'] ?? null,
    'host'     => $_ENV['MARIADB_HOST'] ?? null,
    'driver'   => 'pdo_mysql',
];

if (($_ENV['MYSQL_IAM_AUTH'] ?? null)) {
    $connectionParams['sslmode'] = 'require';
}

try {
    $dbConnection = dbalConnect($connectionParams);
} catch (Exception $e) {
    $stdErr = fopen('php://stderr', 'wb');
    fwrite($stdErr, sprintf("[ERROR] %s", $e->getMessage()));
    fclose($stdErr);
}

migrateMariaDBfNecessary($dbConnection);

print getResults($dbConnection);


// check if table animal_customer exists, if not, run migrations
function migrateMariaDBfNecessary(Connection $dbConnection)
{
    $schemaManager = $dbConnection->createSchemaManager();
    if (!$schemaManager->tablesExist(['animal_customer'])) {
        $migrations = glob(__DIR__ . '/migrations_mariadb/*.sql');
        foreach ($migrations as $migration) {
            $dbConnection->executeStatement(file_get_contents($migration));
        }
    }
}
