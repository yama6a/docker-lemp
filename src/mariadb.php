<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'sql_results.php';

use Doctrine\DBAL\Connection;

// Token gets generated, but somehow the token doesn't allow access to DB. Use user/pw auth for now.
//function getIamToken()
//{
//    $provider         = CredentialProvider::defaultProvider();
//    $RdsAuthGenerator = new Aws\Rds\AuthTokenGenerator($provider);
//
//    return $RdsAuthGenerator->createToken($_ENV['MARIADB_HOST'] . ":5432", "eu-west-1", $_ENV['MARIADB_USER']);
//}
//$password = ($_ENV['MARIADB_IAM_AUTH'] ?? null) ? getIamToken() : $_ENV['MARIADB_PASSWORD'] ?? null;

$connectionParams = [
    'dbname'   => $_ENV['MARIADB_DATABASE'] ?? null,
    'password' => $_ENV['MARIADB_PASSWORD'] ?? null,
    'user'     => $_ENV['MARIADB_USER'] ?? null,
    'host'     => $_ENV['MARIADB_HOST'] ?? null,
    'driver'   => 'pdo_mysql',
];

if (($_ENV['MARIADB_IAM_AUTH'] ?? null)) {
    $connectionParams['sslmode'] = 'require';
}

$dbConnection = dbalConnect($connectionParams);
migrateMariaDBfNecessary($dbConnection);

print sprintf(" Database connection to <b>MariaDB</b> %s!", "successful");
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
