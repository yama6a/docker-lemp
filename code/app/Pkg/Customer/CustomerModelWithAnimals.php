<?php

namespace App\Pkg\Customer;

use App\Pkg\Animal\AnimalModel;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class CustomerModelWithAnimals extends CustomerModel implements JsonSerializable
{
    use HasAnimals {
        jsonSerialize as protected jsonSerializeAnimals;
    }

    /** @param AnimalModel[] $animals */
    public function __construct(private CustomerModel $customer, array $animals) {
        parent::__construct($customer->getId(), $customer->getPhone(), $customer->getFirstName(), $customer->getLastName());
        $this->animals = $animals;
    }

    public function getAnimals(): array { return $this->animals; }

    #[ArrayShape(['id' => "string", 'phone' => "string", 'firstName' => "string", 'lastName' => "string", 'animals' => "AnimalModel[]"])]
    public function jsonSerialize(): array {
        return array_merge(parent::jsonSerialize(), ['animals' => $this->jsonSerializeAnimals()]);
    }
}
