<?php declare(strict_types=1);

namespace App\Pkg\Animal;

use App\Exceptions\NotFoundHttpException;
use App\Exceptions\UnexpectedInternalException;
use App\Exceptions\UnprocessableException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class AnimalRepository
{
    private const COLUMNS = ['id', 'species', 'name', 'color', 'has_fur'];

    public function __construct(private Connection $connection) { }

    /**
     * @param string          $id
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @return AnimalModel
     * @throws NotFoundHttpException|UnexpectedInternalException
     */
    public function find(string $id, Connection $conn = null): AnimalModel {
        if ($conn == null) {
            $conn = $this->connection;
        }

        try {
            $data = $conn->createQueryBuilder()
                ->select(...self::COLUMNS)
                ->from('animals')
                ->where("id = :id")
                ->setParameter("id", $id)
                ->fetchAssociative();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed fetching animal object with id %s.", $id), 0, $e);
        }

        if (!$data) {
            throw new NotFoundHttpException(sprintf("Animal with id %s not found", $id));
        }

        return $this->assocToObject($data);
    }

    /**
     * @return AnimalModel[]
     * @throws UnexpectedInternalException
     */
    public function getAll(): array {
        try {
            $data = $this->connection->createQueryBuilder()->select(...self::COLUMNS)->from('animals')->fetchAllAssociative();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException("Failed fetching all animals.", 0, $e);
        }

        return $this->assocToObjects($data);
    }

    /**
     * @param AnimalModel     $animal
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @return AnimalModel
     * @throws UnexpectedInternalException|NotFoundHttpException|UnprocessableException
     */
    public function insert(AnimalModel $animal, Connection $conn = null): AnimalModel {
        if ($conn == null) {
            $conn = $this->connection;
        }

        try {
            $conn->createQueryBuilder()
                ->insert("animals")
                ->values([
                    'id' => ':id',
                    'species' => ":species",
                    'name' => ":name",
                    'color' => ":color",
                    'has_fur' => ":has_fur",
                ])
                ->setParameter("id", $animal->getId())
                ->setParameter("species", $animal->getSpecies())
                ->setParameter("name", $animal->getName())
                ->setParameter("color", $animal->getColor())
                ->setParameter("has_fur", $animal->HasFur())
                ->executeStatement();
        } catch (UniqueConstraintViolationException) {
            throw new UnprocessableException(sprintf("Animal with id %s already exists.", $animal->getId()));
        } catch (DBALException $e) {
            throw new UnexpectedInternalException("Failed to insert animal into DB", 0, $e);
        }

        return $this->find($animal->getId(), $conn);
    }

    /**
     * @param AnimalModel     $animal
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @return AnimalModel
     * @throws UnexpectedInternalException|NotFoundHttpException
     */
    public function overwrite(AnimalModel $animal, Connection $conn = null): AnimalModel {
        if ($conn == null) {
            $conn = $this->connection;
        }

        // throws NotFoundHttpException if animal does not exist
        // ToDo: This is super inefficient. We should use a simple Exists() query to check for existence.
        //       Or even better with a RETURNING() clause in the UPDATE query to save one round-trip.
        $this->find($animal->getId());

        try {
            $conn->createQueryBuilder()
                ->update("animals")
                ->set('species', ':species')
                ->set('name', ':name')
                ->set('color', ':color')
                ->set('has_fur', ':has_fur')
                ->where("id = :id")
                ->setParameter("species", $animal->getSpecies())
                ->setParameter("name", $animal->getName())
                ->setParameter("color", $animal->getColor())
                ->setParameter("has_fur", $animal->hasFur())
                ->setParameter("id", $animal->getId())
                ->executeStatement();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed updating animal object with id %s.", $animal->getId()), 0, $e);
        }

        return $animal;
    }

    /**
     * @param string          $id
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @throws UnexpectedInternalException|NotFoundHttpException
     */
    public function delete(string $id, Connection $conn = null): void {
        if ($conn == null) {
            $conn = $this->connection;
        }

        try {
            $rowsAffected = $conn->createQueryBuilder()->delete("animals")->where("id = :id")->setParameter("id", $id)->executeStatement();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed deleting animal object with id %s.", $id), 0, $e);
        }

        if ($rowsAffected == 0) {
            throw new NotFoundHttpException(sprintf("Animal with id %s not found", $id));
        }
    }


    /**
     * @param string $customerId
     * @return AnimalModel[]
     * @throws UnexpectedInternalException
     */
    public function getByCustomerId(string $customerId): array {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select(...self::COLUMNS)
                ->from('animals')
                ->join('animals', 'animal_customer', 'ac', 'ac.animal_id = animals.id')
                ->where("customer_id = :customer_id")
                ->orderBy('customer_id', 'ASC')
                ->setParameter("customer_id", $customerId)
                ->fetchAllAssociative();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed fetching all animals for customer with id %s.", $customerId), 0, $e);
        }

        return $this->assocToObjects($data);
    }

    /**
     * @param array $data Assoc Array from DB
     * @return AnimalModel[]
     * @throws UnexpectedInternalException
     */
    private function assocToObjects(array $data): array {
        return array_map(fn(array $row) => $this->assocToObject($row), $data);
    }

    /**
     * @param array $row
     * @return AnimalModel
     * @throws UnexpectedInternalException
     */
    private function assocToObject(array $row): AnimalModel {
        // check if all fields exist in $row and throw error if not
        foreach (self::COLUMNS as $column) {
            if (!array_key_exists($column, $row)) {
                throw new UnexpectedInternalException(sprintf("Column %s not found in assoc array.", $column));
            }
        }

        return new AnimalModel($row['id'], $row['species'], $row['name'], $row['color'], (bool)$row['has_fur']);
    }
}
