<?php declare(strict_types=1);

namespace App\Pkg\Customer;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class CustomerModel implements JsonSerializable
{
    public function __construct(private string $id, private string $phone, private string $firstName, private string $lastName) { }

    public static function fromAssocArray(array $data): CustomerModel {
        return new self($data['id'] ?? "", $data['phone'] ?? "", $data['firstName'] ?? "", $data['lastName'] ?? "");
    }

    public function getId(): string { return $this->id; }

    public function getPhone(): string { return $this->phone; }

    public function getFirstName(): string { return $this->firstName; }

    public function getLastName(): string { return $this->lastName; }

    #[ArrayShape(['id' => "string", 'phone' => "string", 'firstName' => "string", 'lastName' => "string"])]
    public function jsonSerialize(): array {
        return [
            'id'        => $this->id,
            'phone'     => $this->phone,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
        ];
    }
}
