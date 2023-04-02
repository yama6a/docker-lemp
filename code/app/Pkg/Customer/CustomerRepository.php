<?php declare(strict_types=1);

namespace App\Pkg\Customer;

use App\Exceptions\NotFoundHttpException;
use App\Exceptions\UnexpectedInternalException;
use App\Exceptions\UnprocessableException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CustomerRepository
{
    private const COLUMNS = ['id', 'phone', 'first_name', 'last_name'];

    public function __construct(private Connection $connection) { }

    /**
     * @param string          $id
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @return CustomerModel
     * @throws NotFoundHttpException|UnexpectedInternalException
     */
    public function find(string $id, Connection $conn = null): CustomerModel {
        if ($conn == null) {
            $conn = $this->connection;
        }

        try {
            $data = $conn->createQueryBuilder()
                ->select(...self::COLUMNS)
                ->from('customers')
                ->where("id = :id")
                ->setParameter("id", $id)
                ->fetchAssociative();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed fetching customer object with id %s.", $id), 0, $e);
        }

        if (!$data) {
            throw new NotFoundHttpException(sprintf("Customer with id %s not found", $id));
        }

        return $this->assocToObject($data);
    }

    /**
     * @return CustomerModel[]
     * @throws UnexpectedInternalException
     */
    public function getAll(): array {
        try {
            $data = $this->connection->createQueryBuilder()->select(...self::COLUMNS)->from('customers')->fetchAllAssociative();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException("Failed fetching all customers.", 0, $e);
        }

        return $this->assocToObjects($data);
    }

    /**
     * @param CustomerModel   $customer
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @return CustomerModel
     * @throws UnexpectedInternalException|NotFoundHttpException|UnprocessableException
     */
    public function insert(CustomerModel $customer, Connection $conn = null): CustomerModel {
        if ($conn == null) {
            $conn = $this->connection;
        }

        try {
            $conn->createQueryBuilder()
                ->insert("customers")
                ->values([
                    'id'         => ':id',
                    'phone'      => ':phone',
                    'first_name' => ':first_name',
                    'last_name'  => ':last_name',
                ])
                ->setParameter("id", $customer->getId())
                ->setParameter("phone", $customer->getPhone())
                ->setParameter("first_name", $customer->getFirstName())
                ->setParameter("last_name", $customer->getLastName())
                ->executeStatement();
        } catch (UniqueConstraintViolationException) {
            throw new UnprocessableException(sprintf("Animal with id %s already exists.", $customer->getId()));
        } catch (DBALException $e) {
            throw new UnexpectedInternalException("Failed to insert customer into DB", 0, $e);
        }

        return $this->find($customer->getId(), $conn);
    }

    /**
     * @param CustomerModel   $customer
     * @param Connection|null $conn Can be used to wrap the operation in a transaction. null = use default connection.
     * @return CustomerModel
     * @throws UnexpectedInternalException|NotFoundHttpException
     */
    public function overwrite(CustomerModel $customer, Connection $conn = null): CustomerModel {
        if ($conn == null) {
            $conn = $this->connection;
        }

        // throws NotFoundHttpException if customer does not exist
        // ToDo: This is kinda inefficient. We should use a simple Exists() query to check for existence.
        //       Or even better with a RETURNING() clause in the UPDATE query to save one round-trip.
        $this->find($customer->getId());

        try {
            $conn->createQueryBuilder()
                ->update("customers")
                ->set('phone', ':phone')
                ->set('first_name', ':first_name')
                ->set('last_name', ':last_name')
                ->where("id = :id")
                ->setParameter("id", $customer->getId())
                ->setParameter("phone", $customer->getPhone())
                ->setParameter("first_name", $customer->getFirstName())
                ->setParameter("last_name", $customer->getLastName())
                ->executeStatement();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed updating customer object with id %s.", $customer->getId()), 0, $e);
        }

        return $customer;
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
            $rowsAffected = $conn->createQueryBuilder()
                ->delete("customers")
                ->where("id = :id")
                ->setParameter("id", $id)
                ->executeStatement();
        } catch (DBALException $e) {
            throw new UnexpectedInternalException(sprintf("Failed deleting customer object with id %s.", $id), 0, $e);
        }

        if ($rowsAffected == 0) {
            throw new NotFoundHttpException(sprintf("Customer with id %s not found", $id));
        }
    }

    /**
     * @param array $data Assoc Array from DB
     * @return CustomerModel[]
     * @throws UnexpectedInternalException
     */
    private function assocToObjects(array $data): array {
        return array_map(fn(array $row) => $this->assocToObject($row), $data);
    }

    /**
     * @param array $row
     * @return CustomerModel
     * @throws UnexpectedInternalException
     */
    private function assocToObject(array $row): CustomerModel {
        // check if all fields exist in $row and throw error if not
        foreach (self::COLUMNS as $column) {
            if (!array_key_exists($column, $row)) {
                throw new UnexpectedInternalException(sprintf("Column %s not found in assoc array.", $column));
            }
        }

        return new CustomerModel($row['id'], $row['phone'], $row['first_name'], $row['last_name']);
    }
}
