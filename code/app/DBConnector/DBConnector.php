<?php declare(strict_types=1);

namespace App\DBConnector;

use App\Exceptions\UnexpectedInternalException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use PDOException;

class DBConnector
{
    public static function getMySqlConnection(): Connection {
        return self::getConnection([
            'driver'   => 'mysqli',
            'host'     => $_ENV['MYSQL_HOST'] ?? 'localhost',
            'port'     => $_ENV['MYSQL_PORT'] ?? 3306,
            'dbname'   => $_ENV['MYSQL_DATABASE'] ?? null,
            'user'     => $_ENV['MYSQL_USER'] ?? null,
            'password' => $_ENV['MYSQL_PASSWORD'] ?? null,
        ]);
    }

    public static function getMariaDbConnection(): Connection {
        return self::getConnection([
            'driver'   => 'pdo_mysql',
            'host'     => $_ENV['MARIADB_HOST'] ?? 'localhost',
            'port'     => $_ENV['MARIADB_PORT'] ?? 3306,
            'dbname'   => $_ENV['MARIADB_DATABASE'] ?? null,
            'user'     => $_ENV['MARIADB_USER'] ?? null,
            'password' => $_ENV['MARIADB_PASSWORD'] ?? null,
        ]);
    }

    public static function getPostgresDbConnection(): Connection {
        return self::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => $_ENV['POSTGRES_HOST'] ?? 'localhost',
            'port'     => $_ENV['POSTGRES_PORT'] ?? 5432,
            'dbname'   => $_ENV['POSTGRES_DATABASE'] ?? null,
            'user'     => $_ENV['POSTGRES_USER'] ?? null,
            'password' => $_ENV['POSTGRES_PASSWORD'] ?? null,
        ]);
    }

    /** @throws UnexpectedInternalException */
    private static function getConnection(array $connectionParams): Connection {
        try {
            $dbConnection = DriverManager::getConnection($connectionParams);
            if (!$dbConnection->connect() or !$dbConnection->isConnected()) {
                throw new UnexpectedInternalException(sprintf("Database connection to %s:%d via %s failed!",
                    $connectionParams['host'],
                    $connectionParams['port'],
                    $connectionParams['driver']));
            }
        } catch (PDOException|DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Database Exception when trying to connect to %s:%d via %s",
                $connectionParams['host'],
                $connectionParams['port'],
                $connectionParams['driver']));
        }
        return $dbConnection;
    }
}
