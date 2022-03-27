<?php
namespace App\Domain\Services;
use App\Domain\Repository\TestRepo;

/**
* Service.
*/
final class TestService
{
/**
* @var UserCreatorRepository
*/
private $repository;
/**
* The constructor.
*
* @param 
   

 */

public function __construct(TestRepo $repository)
{
$this->repository = $repository;
}
/**
* Create a new user.
*
* @param array $data The form data
*
* @return int The new user ID
*/

public function getProduct(): array
{
return $this->repository->getProduct();

}


}