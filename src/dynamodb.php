<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

print getDynamoResults([
    'region'   => $_ENV['AWS_REGION'] ?? "",
    'version'  => 'latest',
    'endpoint' => $_ENV['DYNAMODB_ENDPOINT'],
]);

function getDynamoResults(array $connectionParams)
{
    $output        = "";
    $isDbConnected = true;
    try {
        $client    = new DynamoDbClient($connectionParams);
        $marshaler = new Marshaler();
        migrateIfNecessary($client, $marshaler);
    } catch (Exception $e) {
        $isDbConnected = false;
        $output        = $e->getMessage();
    }

    $output .= sprintf(" Database connection to <b>DynamoDB</b> %s!", $isDbConnected ? "successful" : "failed");
    if (!$isDbConnected) {
        return $output;
    }


    $output   .= "<ul>";
    $iterator = $client->getIterator('Scan', ['TableName' => 'my-awesome-service.animal_customer']);
    foreach ($iterator as $row) {
        $obj = $marshaler->unmarshalItem($row);
        foreach ($obj['pets'] as $pet) {
            $output .= "<li>";
            $output .= htmlspecialchars(sprintf("%s %s has %u %s %s %s %s.",
                $obj['first_name'],
                $obj['last_name'],
                $pet['count'],
                $pet['has_fur'] ? "hairy" : "smooth",
                $pet['color'],
                $pet['species'],
                Str::plural($pet['name'], $pet['count'])
            ));
            $output .= "</li>";
        }
    }
    $output .= "</ul>";

    return $output;
}

function migrateIfNecessary(DynamoDbClient $client, Marshaler $marshaler)
{
    $iterator = $client->getIterator('ListTables');

    foreach ($iterator as $tableName) {
        if ($tableName === 'my-awesome-service.animal_customer') {
            return; // no need to migrate
        }
    }


    $client->createTable([
        'TableName'            => 'my-awesome-service.animal_customer',
        'AttributeDefinitions' => [['AttributeName' => 'id', 'AttributeType' => 'S']],
        'KeySchema'            => [['AttributeName' => 'id', 'KeyType' => 'HASH']],
        'BillingMode'          => 'PAY_PER_REQUEST',
    ]);

    $client->waitUntil('TableExists', [
        'TableName' => 'my-awesome-service.animal_customer',
    ]);

    $client->putItem([
        'TableName' => 'my-awesome-service.animal_customer',
        'Item'      => $marshaler->marshalJson(json_encode([
            'id'         => Uuid::uuid4()->toString(),
            'phone'      => "+46 - 72 886 1234",
            'first_name' => 'Dwayne',
            'last_name'  => 'Johnson',
            'pets'       => [
                [
                    'count'   => rand(1, 5),
                    'id'      => 1,
                    'species' => 'feline',
                    'name'    => 'bobcat',
                    'color'   => 'beige-ish?',
                    'has_fur' => true,
                ],
                [
                    'count'   => rand(1, 5),
                    'id'      => 2,
                    'species' => 'feline',
                    'name'    => 'panther',
                    'color'   => 'black',
                    'has_fur' => true,
                ],
            ],
        ])),
    ]);

    $client->putItem([
        'TableName' => 'my-awesome-service.animal_customer',
        'Item'      => $marshaler->marshalJson(json_encode([
            'id'         => Uuid::uuid4()->toString(),
            'phone'      => '+1 - 555 1234',
            'first_name' => 'John',
            'last_name'  => 'Cena',
            'pets'       => [
                [
                    'count'   => rand(1, 5),
                    'id'      => 3,
                    'species' => 'reptilian',
                    'name'    => 'king cobra',
                    'color'   => 'olive green',
                    'has_fur' => false,
                ],
            ],
        ])),
    ]);

    $client->putItem([
        'TableName' => 'my-awesome-service.animal_customer',
        'Item'      => $marshaler->marshalJson(json_encode([
            'id'         => Uuid::uuid4()->toString(),
            'phone'      => '+30 - 443 1122',
            'first_name' => 'Steve',
            'last_name'  => 'Austin',
            'pets'       => [
                [
                    'count'   => rand(1, 5),
                    'id'      => 4,
                    'species' => 'amphibian',
                    'name'    => 'poison dart frog',
                    'color'   => 'red',
                    'has_fur' => false,
                ],
            ],
        ])),
    ]);

    $client->putItem([
        'TableName' => 'my-awesome-service.animal_customer',
        'Item'      => $marshaler->marshalJson(json_encode([
            'id'         => Uuid::uuid4()->toString(),
            'phone'      => '+555 - 998 1222',
            'first_name' => 'Rey',
            'last_name'  => 'Mysterio',
            'pets'       => [
                [
                    'count'   => rand(1, 5),
                    'id'      => 5,
                    'species' => 'canine',
                    'name'    => 'fox',
                    'color'   => 'orange',
                    'has_fur' => true,
                ], [
                    'count'   => rand(1, 5),
                    'id'      => 6,
                    'species' => 'canine',
                    'name'    => 'chihuahua',
                    'color'   => 'brown',
                    'has_fur' => true,
                ],
            ],
        ])),
    ]);
}
