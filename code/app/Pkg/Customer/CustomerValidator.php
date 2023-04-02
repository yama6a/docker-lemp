<?php

namespace App\Pkg\Customer;

use App\Exceptions\UnprocessableException;
use Ramsey\Uuid\Uuid;

class CustomerValidator
{
    /**
     * @param CustomerModel $customer
     * @return void
     * @throws UnprocessableException
     */
    public function validate(CustomerModel $customer): void {
        // ToDo: don't fail on first error, but collect all errors and throw them all at once (e.g. in an array)
        $this->validateId($customer->getId());
        $this->validateFirstName($customer->getFirstName());
        $this->validateLastName($customer->getLastName());
        $this->validatePhone($customer->getPhone());
    }

    /** @throws UnprocessableException */
    public function validateId(string $id): void {
        if (!Uuid::isValid($id)) {
            throw new UnprocessableException("customer.id is not a valid UUID.");
        }
    }

    /** @throws UnprocessableException */
    public function validateFirstName(string $name): void {
        if (strlen($name) < 2) {
            throw new UnprocessableException("customer.firstName is too short.");
        }
    }

    /** @throws UnprocessableException */
    public function validateLastName(string $name): void {
        if (strlen($name) < 2) {
            throw new UnprocessableException("customer.lastName is too short.");
        }
    }

    /** @throws UnprocessableException */
    public function validatePhone(string $phone): void {
        if (!str_starts_with($phone, "+")) {
            throw new UnprocessableException("customer.phone must start with a '+'.");
        }

        if (!ctype_digit(substr($phone, 1))) {
            throw new UnprocessableException("customer.phone can only contain digits after the '+'.");
        }

        if (strlen($phone) < 10) {
            throw new UnprocessableException("customer.phone is too short (must have at least 9 digits).");
        }
    }
}
