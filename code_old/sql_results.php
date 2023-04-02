<?php declare(strict_types=1);

// Don't hate me for this file. It's ugly, I know!
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException as DBALException;
use Illuminate\Support\Str;

function getResults(Connection $dbConnection): string
{
    $data = $dbConnection->createQueryBuilder()
        ->select('*')
        ->from('animal_customer')
        ->leftJoin('animal_customer', 'customers', 'customers', 'customers.id = animal_customer.customer_id')
        ->leftJoin('animal_customer', 'animals', 'animals', 'animal_customer.animal_id = animals.id')
        ->fetchAllAssociative();

    // sort array by customer ID
    usort($data, fn(array $row1, array $row2) => $row1['customer_id'] ?? 0 <=> $row2['customer_id'] ?? 0);

    $output = "<ul>";

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

/**
 * @param array $connectionParams
 * @return Connection
 */
function dbalConnect(array $connectionParams): Connection
{
    $isDbConnected = false;
    try {
        $dbConnection  = DriverManager::getConnection($connectionParams);
        $isDbConnected = $dbConnection->connect() && $dbConnection->isConnected();
    } catch (PDOException|DBALException $e) {
        handleException($e->getMessage());
    }

    if (!$isDbConnected) {
        handleException(sprintf(" Database connection to <b>%s</b> via <b>%s</b> failed!", $connectionParams['host'], $connectionParams['driver']));
    }

    return $dbConnection;
}

function handleException(string $message): void
{
    http_response_code(500);
    $stdErr = fopen('php://stderr', 'wb');
    fwrite($stdErr, sprintf("[ERROR] %s", $message));
    fclose($stdErr);
    print ($message);
    exit(1);
}
