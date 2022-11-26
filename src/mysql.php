<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'sql_results.php';

print getResults([
    'dbname'   => $_ENV['MYSQL_DATABASE'] ?? null,
    'password' => $_ENV['MYSQL_PASSWORD'] ?? null,
    'user'     => $_ENV['MYSQL_USER'] ?? null,
    'host'     => $_ENV['MYSQL_HOST'] ?? null,
    'driver'   => 'mysqli',
]);
