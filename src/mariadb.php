<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'sql_results.php';

print getResults([
    'dbname'   => $_ENV['MARIADB_DATABASE'] ?? null,
    'password' => $_ENV['MARIADB_PASSWORD'] ?? null,
    'user'     => $_ENV['MARIADB_USER'] ?? null,
    'host'     => $_ENV['MARIADB_HOST'] ?? null,
    'driver'   => 'pdo_mysql',
]);
