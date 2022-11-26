<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'sql_results.php';

print getResults([
    'dbname'   => $_ENV['POSTGRES_DATABASE'] ?? null,
    'password' => $_ENV['POSTGRES_PASSWORD'] ?? null,
    'user'     => $_ENV['POSTGRES_USER'] ?? null,
    'host'     => $_ENV['POSTGRES_HOST'] ?? null,
    'driver'   => 'pdo_pgsql',
]);
