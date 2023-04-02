<?php

namespace App\Pkg\Animal;

use App\Exceptions\UnprocessableException;
use Ramsey\Uuid\Uuid;

class AnimalValidator
{
    /**
     * Validates the given animal object.
     *
     * @throws UnprocessableException
     */
    public function validate(AnimalModel $animal): void {
        // ToDo: don't fail on first error, but collect all errors and throw them all at once (e.g. in an array)
        static::validateId($animal->getId());
        $this->validateSpecies($animal->getSpecies());
        $this->validateName($animal->getName());
        $this->validateColor($animal->getColor());
    }

    /** @throws UnprocessableException */
    private function validateId(string $id): void {
        if (!Uuid::isValid($id)) {
            throw new UnprocessableException("animal.id is not a valid UUID.");
        }
    }

    /** @throws UnprocessableException */
    private function validateSpecies(string $species): void {
        if (strlen($species) < 1) {
            throw new UnprocessableException("animal.species cannot be empty.");
        }
    }

    /** @throws UnprocessableException */
    private function validateName(string $name): void {
        if (strlen($name) < 1) {
            throw new UnprocessableException("animal.name cannot be empty.");
        }
    }

    /** @throws UnprocessableException */
    private function validateColor(string $color): void {
        if (strlen($color) < 1) {
            throw new UnprocessableException("animal.color cannot be empty.");
        }
    }
}
