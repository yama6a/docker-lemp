<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

print getDynamoResults([
    'region'      => $_ENV['AWS_REGION'],
    'version'     => 'latest',
    'endpoint'    => $_ENV['DYNAMODB_ENDPOINT'],
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
]);

function getDynamoResults(array $connectionParams)
{
    $output        = "";
    $isDbConnected = true;
    try {
        $client = new DynamoDbClient($connectionParams);
        migrateIfNecessary($client);
    } catch (Exception $e) {
        $isDbConnected = false;
        $output        = $e->getMessage();
    }

    $output .= sprintf(" Database connection to <b>DynamoDB</b> %s!", $isDbConnected ? "successful" : "failed");
    if (!$isDbConnected) {
        return $output;
    }


    $output   .= "<ul>";
    $iterator = $client->getIterator('Scan', ['TableName' => 'animal_customer']);
    foreach ($iterator as $row) {
        foreach ($row['pets']['L'] as $pet) {
            $pet = $pet['M'];
            $output .= "<li>";
            $output .= htmlspecialchars(sprintf("%s %s has %u %s %s %s %s.",
                $row['first_name']['S'],
                $row['last_name']['S'],
                $pet['count']['N'],
                $pet['has_fur']['BOOL'] ? "hairy" : "smooth",
                $pet['color']['S'],
                $pet['species']['S'],
                Str::plural($pet['name']['S'], $pet['count']['N'])
            ));
            $output .= "</li>";
        }
    }
    $output .= "</ul>";

    return $output;
}

function migrateIfNecessary(DynamoDbClient $client)
{
    $iterator = $client->getIterator('ListTables');

    foreach ($iterator as $tableName) {
        if ($tableName === 'animal_customer') {
            return; // no need to migrate
        }
    }


    $client->createTable([
        'TableName'             => 'animal_customer',
        'AttributeDefinitions'  => [
            [
                'AttributeName' => 'id',
                'AttributeType' => 'S',
            ],
        ],
        'KeySchema'             => [
            [
                'AttributeName' => 'id',
                'KeyType'       => 'HASH',
            ],
        ],
        'BillingMode' => 'PAY_PER_REQUEST',
    ]);

    $client->waitUntil('TableExists', [
        'TableName' => 'animal_customer',
    ]);

    $client->putItem([
        'TableName' => 'animal_customer',
        'Item'      => [
            'id'         => ['S' => Uuid::uuid4()->toString()],
            'phone'      => ['S' => "+46 - 72 886 1234"],
            'first_name' => ['S' => 'Dwayne'],
            'last_name'  => ['S' => 'Johnson'],
            'pets'       => ['L' => [
                ['M' => [
                    'count'   => ['N' => rand(1, 5)],
                    'id'      => ['N' => 1],
                    'species' => ['S' => 'feline'],
                    'name'    => ['S' => 'bobcat'],
                    'color'   => ['S' => 'beige-ish?'],
                    'has_fur' => ['BOOL' => true],
                ]], ['M' => [
                    'count'   => ['N' => rand(1, 5)],
                    'id'      => ['N' => 2],
                    'species' => ['S' => 'feline'],
                    'name'    => ['S' => 'panther'],
                    'color'   => ['S' => 'black'],
                    'has_fur' => ['BOOL' => true],
                ]],
            ]],
        ],
    ]);

    $client->putItem([
        'TableName' => 'animal_customer',
        'Item'      => [
            'id'         => ['S' => Uuid::uuid4()->toString()],
            'phone'      => ['S' => '+1 - 555 1234'],
            'first_name' => ['S' => 'John'],
            'last_name'  => ['S' => 'Cena'],
            'pets'       => ['L' => [
                ['M' => [
                    'count'   => ['N' => rand(1, 5)],
                    'id'      => ['N' => 1],
                    'species' => ['S' => 'reptilian'],
                    'name'    => ['S' => 'king cobra'],
                    'color'   => ['S' => 'olive green'],
                    'has_fur' => ['BOOL' => false],
                ]],
            ]],
        ],
    ]);

    $client->putItem([
        'TableName' => 'animal_customer',
        'Item'      => [
            'id'         => ['S' => Uuid::uuid4()->toString()],
            'phone'      => ['S' => '+30 - 443 1122'],
            'first_name' => ['S' => 'Steve'],
            'last_name'  => ['S' => 'Austin'],
            'pets'       => ['L' => [
                ['M' => [
                    'count'   => ['N' => rand(1, 5)],
                    'id'      => ['N' => 1],
                    'species' => ['S' => 'amphibian'],
                    'name'    => ['S' => 'poison dart frog'],
                    'color'   => ['S' => 'red'],
                    'has_fur' => ['BOOL' => false],
                ]],
            ]],
        ],
    ]);

    $client->putItem([
        'TableName' => 'animal_customer',
        'Item'      => [
            'id'         => ['S' => Uuid::uuid4()->toString()],
            'phone'      => ['S' => '+555 - 998 1222'],
            'first_name' => ['S' => 'Rey'],
            'last_name'  => ['S' => 'Mysterio'],
            'pets'       => ['L' => [
                ['M' => [
                    'count'   => ['N' => rand(1, 5)],
                    'id'      => ['N' => 1],
                    'species' => ['S' => 'canine'],
                    'name'    => ['S' => 'fox'],
                    'color'   => ['S' => 'orange'],
                    'has_fur' => ['BOOL' => true],
                ]], ['M' => [
                    'count'   => ['N' => rand(1, 5)],
                    'id'      => ['N' => 2],
                    'species' => ['S' => 'canine'],
                    'name'    => ['S' => 'chihuahua'],
                    'color'   => ['S' => 'brown'],
                    'has_fur' => ['BOOL' => true],
                ]],
            ]],
        ],
    ]);
}
