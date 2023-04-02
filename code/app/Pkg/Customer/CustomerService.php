<?php declare(strict_types=1);

namespace App\Pkg\Customer;

use App\Exceptions\NotFoundHttpException;
use App\Exceptions\UnexpectedInternalException;
use App\Exceptions\UnprocessableException;

class CustomerService
{
    public function __construct(private CustomerRepository $customerRepository, private CustomerValidator $customerValidator) { }

    /**
     * @return CustomerModel[]
     * @throws UnexpectedInternalException
     */
    public function getAllCustomers(): array {
        return $this->customerRepository->getAll();
    }

    /** @throws NotFoundHttpException|UnexpectedInternalException */
    public function getCustomer(string $id): CustomerModel {
        return $this->customerRepository->find($id);
    }

    /** @throws UnexpectedInternalException|UnprocessableException */
    public function createCustomer(CustomerModel $customer): CustomerModel {
        $this->customerValidator->validate($customer);
        try {
            return $this->customerRepository->insert($customer);
        } catch (NotFoundHttpException $e) {
            // this should never happen, because we just created the object. Hence, the UnexpectedInternalException
            throw new UnexpectedInternalException("Could not find created customer object; something went wrong.", 0, $e);
        }
    }

    /** @throws NotFoundHttpException|UnexpectedInternalException|UnprocessableException */
    public function overwriteCustomer(CustomerModel $customer): CustomerModel {
        $this->customerValidator->validate($customer);
        return $this->customerRepository->overwrite($customer);
    }

    /** @throws NotFoundHttpException|UnexpectedInternalException */
    public function deleteCustomer(string $id): void {
        $this->customerRepository->delete($id);
    }
}
