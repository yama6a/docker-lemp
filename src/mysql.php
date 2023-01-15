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
//    return $RdsAuthGenerator->createToken($_ENV['MYSQL_HOST'] . ":5432", "eu-west-1", $_ENV['MYSQL_USER']);
//}
//$password = ($_ENV['MYSQL_IAM_AUTH'] ?? null) ? getIamToken() : $_ENV['MYSQL_PASSWORD'] ?? null;

$connectionParams = [
    'dbname'   => $_ENV['MYSQL_DATABASE'] ?? null,
    'password' => $_ENV['MYSQL_PASSWORD'] ?? null,
    'user'     => $_ENV['MYSQL_USER'] ?? null,
    'host'     => $_ENV['MYSQL_HOST'] ?? null,
    'driver'   => 'pdo_mysql',
];

if (($_ENV['MYSQL_IAM_AUTH'] ?? null)) {
    $connectionParams['sslmode'] = 'require';
}


$dbConnection = dbalConnect($connectionParams);
migrateMySQLIfNecessary($dbConnection);

print sprintf(" Database connection to <b>MySQL</b> %s!", "successful");
print getResults($dbConnection);


// check if table animal_customer exists, if not, run migrations
function migrateMySQLIfNecessary(Connection $dbConnection)
{
    $schemaManager = $dbConnection->createSchemaManager();
    if (!$schemaManager->tablesExist(['animal_customer'])) {
        $migrations = glob(__DIR__ . '/migrations_mysql/*.sql');
        foreach ($migrations as $migration) {
            $dbConnection->executeStatement(file_get_contents($migration));
        }
    }
}
