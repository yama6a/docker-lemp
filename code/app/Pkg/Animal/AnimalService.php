<?php declare(strict_types=1);

namespace App\Pkg\Animal;

use App\Exceptions\NotFoundHttpException;
use App\Exceptions\UnexpectedInternalException;
use App\Exceptions\UnprocessableException;

class AnimalService
{
    public function __construct(private AnimalRepository $animalRepository, private AnimalValidator $animalValidator) { }

    /**
     * @return AnimalModel[]
     * @throws UnexpectedInternalException
     */
    public function getAllAnimals(): array {
        return $this->animalRepository->getAll();
    }

    /**
     * @param int $id
     * @return AnimalModel
     * @throws NotFoundHttpException|UnexpectedInternalException
     */
    public function getAnimal(string $id): AnimalModel {
        return $this->animalRepository->find($id);
    }

    /**
     * @return AnimalModel[]
     * @throws UnexpectedInternalException
     */
    public function getAnimalsByCustomerId(string $customerId): array {
        return $this->animalRepository->getByCustomerId($customerId);
    }

    /**
     * @param AnimalModel $animal
     * @return AnimalModel
     * @throws UnexpectedInternalException|UnprocessableException
     */
    public function createAnimal(AnimalModel $animal): AnimalModel {
        $this->animalValidator->validate($animal);
        try {
            return $this->animalRepository->insert($animal);
        } catch (NotFoundHttpException $e) {
            // this should never happen, because we just created the object. Hence, the UnexpectedInternalException
            throw new UnexpectedInternalException("Could not find created animal object; something went wrong.", 0, $e);
        }
    }

    /**
     * @param AnimalModel $animal
     * @return AnimalModel
     * @throws NotFoundHttpException|UnexpectedInternalException|UnprocessableException
     */
    public function overwriteAnimal(AnimalModel $animal): AnimalModel {
        $this->animalValidator->validate($animal);
        return $this->animalRepository->overwrite($animal);
    }

    /**
     * @param string $id
     * @return AnimalModel
     * @throws NotFoundHttpException|UnexpectedInternalException
     */
    public function deleteAnimal(string $id): void {
        $this->animalRepository->delete($id);
    }
}
