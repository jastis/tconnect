<?php
namespace App\Database;
use Cake\Database\Connection;
/**
* Transaction handler.
*/
final class Transaction implements TransactionInterface
{
/**
* @var Connection The database connection
*/

private $connection;
public function __construct(Connection $connection)
{
    $this->connection = $connection;
    }
    public function begin(): void
    {
    $this->connection->begin();
    }
    public function commit(): void
    {
        $this->connection->commit();
    }
    public function rollback(): void
    {
    $this->connection->rollback();
    }
}