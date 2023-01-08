<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'sql_results.php';

use Aws\Credentials\CredentialProvider;

// Token gets generated, but somehow the token doesn't allow access to DB. Use user/pw auth for now.
//function getIamToken()
//{
//    $provider         = CredentialProvider::defaultProvider();
//    $RdsAuthGenerator = new Aws\Rds\AuthTokenGenerator($provider);
//
//    return $RdsAuthGenerator->createToken($_ENV['POSTGRES_HOST'] . ":5432", "eu-west-1", $_ENV['POSTGRES_USER']);
//}
//$password = ($_ENV['POSTGRES_IAM_AUTH'] ?? null) ? getIamToken() : $_ENV['POSTGRES_PASSWORD'] ?? null;

$connectionParams = [
    'dbname'   => $_ENV['POSTGRES_DATABASE'] ?? null,
    'password' => $_ENV['POSTGRES_PASSWORD'] ?? null,
    'user'     => $_ENV['POSTGRES_USER'] ?? null,
    'host'     => $_ENV['POSTGRES_HOST'] ?? null,
    'driver'   => 'pdo_pgsql',
];

if(($_ENV['POSTGRES_IAM_AUTH'] ?? null)){
    $connectionParams['sslmode'] = 'require';
}

print_r($connectionParams);

print getResults($connectionParams);
