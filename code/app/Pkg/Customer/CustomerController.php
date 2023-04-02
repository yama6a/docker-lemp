<?php declare(strict_types=1);

namespace App\Pkg\Customer;

use App\Exceptions\NotFoundHttpException;
use App\Exceptions\UnexpectedInternalException;
use App\Exceptions\UnprocessableException;
use App\Http\Controller;
use App\Http\ResponseFactory;
use App\Http\Route;
use App\Pkg\Animal\AnimalService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CustomerController implements Controller
{
    public function __construct(
        private CustomerService $customerService,
        private AnimalService   $animalService
    ) {
    }

    public function routes(): array {
        return [
            Route::GET("/customers", fn(Request $request): JsonResponse => $this->getAll($request)),
            Route::GET("/customers/find", fn(Request $request): JsonResponse => $this->find($request)),
            Route::GET("/customers/findWithAnimals", fn(Request $request): JsonResponse => $this->findWithAnimals($request)),
            Route::POST("/customers", fn(Request $request): JsonResponse => $this->create($request)),
            Route::PUT("/customers", fn(Request $request): JsonResponse => $this->update($request)),
            Route::DELETE("/customers", fn(Request $request): JsonResponse => $this->delete($request)),
        ];
    }

    /** @throws UnexpectedInternalException */
    public function getAll(Request $request): JsonResponse {
        return ResponseFactory::make($this->customerService->getAllCustomers());
    }

    /** @throws NotFoundHttpException|UnexpectedInternalException|UnprocessableException */
    public function find(Request $request): JsonResponse {
        $customerId = $request->get('id');
        if ($customerId === null or $customerId === '') {
            throw new UnprocessableException("missing field 'id' in request.");
        }

        return ResponseFactory::make($this->customerService->getCustomer($customerId));
    }

    /** @throws UnexpectedInternalException|UnprocessableException */
    public function create(Request $request): JsonResponse {
        $customer = CustomerModel::fromAssocArray($request->toArray());
        return ResponseFactory::make($this->customerService->createCustomer($customer));
    }

    /** @throws NotFoundHttpException|UnprocessableException|UnexpectedInternalException */
    public function update(Request $request): JsonResponse {
        $customer = CustomerModel::fromAssocArray($request->toArray());
        return ResponseFactory::make($this->customerService->overwriteCustomer($customer));
    }

    /** @throws NotFoundHttpException|UnexpectedInternalException|UnprocessableException */
    public function delete(Request $request): JsonResponse {
        $customerId = $request->get('id');
        if ($customerId === null or $customerId === '') {
            throw new UnprocessableException("missing field 'id' in request.");
        }

        $this->customerService->deleteCustomer($customerId);
        return ResponseFactory::make("customer deleted successfully");
    }

    /** @throws NotFoundHttpException|UnexpectedInternalException|UnprocessableException */
    private function findWithAnimals(Request $request): JsonResponse {
        $customerId = $request->get('id');
        if ($customerId === null or $customerId === '') {
            throw new UnprocessableException("missing field 'id' in request.");
        }

        $customer = $this->customerService->getCustomer($customerId);
        $animals  = $this->animalService->getAnimalsByCustomerId($customerId);

        return ResponseFactory::make(new CustomerModelWithAnimals($customer, $animals));
    }
}
