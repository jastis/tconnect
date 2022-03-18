<?php

namespace App\Domain\Repository;

use App\Domain\User\Data\UserData;
use App\Factory\QueryFactory;
use Cake\Database\StatementInterface;

/**
 * Repository.
 */
final class TestRepo
{
    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public  function getProduct(): array
    {
        $result = [];

        $query = $this->queryFactory->newSelect('products');
        $query->select('*');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
   
}