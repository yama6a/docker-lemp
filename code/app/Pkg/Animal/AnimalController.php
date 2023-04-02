<?php declare(strict_types=1);

namespace App\Pkg\Animal;

use App\Exceptions\NotFoundHttpException;
use App\Exceptions\UnexpectedInternalException;
use App\Exceptions\UnprocessableException;
use App\Http\Controller;
use App\Http\ResponseFactory;
use App\Http\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AnimalController implements Controller
{
    public function __construct(private AnimalService $animalService) { }

    public function routes(): array {
        return [
            Route::GET("/animals", fn(Request $request): Response => $this->getAll($request)),
            Route::GET("/animals/find", fn(Request $request): Response => $this->find($request)),
            Route::GET("/animals/byCustomer", fn(Request $request): Response => $this->getByCustomerId($request)),
            Route::POST("/animals", fn(Request $request): Response => $this->create($request)),
            Route::PUT("/animals", fn(Request $request): Response => $this->update($request)),
            Route::DELETE("/animals", fn(Request $request): Response => $this->delete($request)),
        ];
    }

    /**
     * Returns all animals.
     *
     * @throws UnexpectedInternalException
     */
    public function getAll(Request $request): JsonResponse {
        return ResponseFactory::make($this->animalService->getAllAnimals());
    }

    /**
     * Returns an animal by id.
     *
     * @throws NotFoundHttpException|UnexpectedInternalException|UnprocessableException
     */
    public function find(Request $request): JsonResponse {
        $animalId = $request->get('id');
        if ($animalId === null or $animalId === '') {
            throw new UnprocessableException("missing field 'id' in request.");
        }

        return ResponseFactory::make($this->animalService->getAnimal($animalId));
    }

    /**
     * Returns all animals by customer id.
     *
     * @throws UnexpectedInternalException
     */
    public function getByCustomerId(Request $request): JsonResponse {
        $customerId = $request->get('customerId');
        if ($customerId === null or $customerId === '') {
            throw new UnprocessableException("missing field 'customerId' in request.");
        }

        return ResponseFactory::make($this->animalService->getAnimalsByCustomerId($customerId));
    }

    /**
     * Creates and returns a new animal.
     *
     * @throws UnexpectedInternalException|UnprocessableException
     */
    public function create(Request $request): JsonResponse {
        $animal = AnimalModel::fromAssocArray($request->toArray());
        return ResponseFactory::make($this->animalService->createAnimal($animal));
    }

    /**
     * Updates and returns an existing animal.
     *
     * @throws NotFoundHttpException|UnprocessableException|UnexpectedInternalException
     */
    public function update(Request $request): JsonResponse {
        $animal = AnimalModel::fromAssocArray($request->toArray());
        return ResponseFactory::make($this->animalService->overwriteAnimal($animal));
    }

    /**
     * Deletes an existing animal.
     *
     * @throws NotFoundHttpException|UnexpectedInternalException
     */
    public function delete(Request $request): JsonResponse {
        $animalId = $request->get('id');
        if ($animalId === null or $animalId === '') {
            throw new UnprocessableException("missing field 'id' in request.");
        }

        $this->animalService->deleteAnimal($animalId);
        return ResponseFactory::make("animal deleted successfully");
    }
}
