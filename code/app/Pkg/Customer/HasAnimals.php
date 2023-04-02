<?php

namespace App\Pkg\Customer;

use App\Pkg\Animal\AnimalModel;

trait HasAnimals
{
    /** @var AnimalModel[] */
    private array $animals = [];

    public function getAnimals(): array { return $this->animals; }

    public function jsonSerialize() {
        return array_map(fn(AnimalModel $animal) => $animal->jsonSerialize(), $this->animals);
    }
}
