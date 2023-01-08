<?php

declare(strict_types=1);

// Don't hate me for this file. It's ugly, I know!
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Illuminate\Support\Str;

function getResults(array $connectionParams)
{
    $isDbConnected = false;
    try {
        $dbConnection  = DriverManager::getConnection($connectionParams);
        $isDbConnected = $dbConnection->connect() && $dbConnection->isConnected();
    } catch (PDOException | \Doctrine\DBAL\Exception $e) {
        $stdErr = fopen('php://stderr', 'wb');
        fwrite($stdErr, sprintf("[ERROR] %s", $e->getMessage()));
        fclose($stdErr);
        // ToDo: write stack-trace to Monolog or some such
    }

    $output = sprintf(
        " Database connection to <b>%s</b> via <b>%s</b> %s!",
        $connectionParams['host'],
        $connectionParams['driver'],
        $isDbConnected ? "successful" : "failed"
    );

    if (!$isDbConnected) {
        http_response_code(500);
        print $output;
        exit(1);
    }

    if ($connectionParams['driver'] === 'pdo_pgsql') {
        migratePgIfNecessary($dbConnection);
    }

    $data = $dbConnection->createQueryBuilder()
        ->select('*')
        ->from('animal_customer')
        ->leftJoin('animal_customer', 'customers', 'customers', 'customers.id = animal_customer.customer_id')
        ->leftJoin('animal_customer', 'animals', 'animals', 'animal_customer.animal_id = animals.id')
        ->fetchAllAssociative();

    // sort array by customer ID
    usort($data, fn(array $row1, array $row2) => $row1['customer_id'] ?? 0 <=> $row2['customer_id'] ?? 0);

    $output .= "<ul>";

    $currentCustomerId = 0;
    foreach ($data as $row) {
        $output .= "<li>";
        $output .= htmlspecialchars(sprintf("%s %s has %u %s %s %s %s.",
            $row['first_name'],
            $row['last_name'],
            $row['count'],
            $row['has_fur'] ? "hairy" : "smooth",
            $row['color'],
            $row['species'],
            Str::plural($row['name'], $row['count'])
        ));
        $output .= "</li>";
    }

    $output .= "</ul>";

    return $output;
}

// check if table animal_customer exists, if not, run migrations from pg_migrations/*.sql
function migratePgIfNecessary(Connection $dbConnection)
{
    $schemaManager = $dbConnection->createSchemaManager();
    if (!$schemaManager->tablesExist(['animal_customer'])) {
        $migrations = glob(__DIR__ . '/pg_migrations/*.sql');
        foreach ($migrations as $migration) {
            $dbConnection->executeStatement(file_get_contents($migration));
        }
    }
}
