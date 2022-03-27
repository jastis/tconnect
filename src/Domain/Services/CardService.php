<?php

namespace App\Domain\Services;


use App\Domain\Repository\CardRepository;
use PHPMailer\PHPMailer\PHPMailer;
use App\Factory\LoggerFactory;
use App\Database\TransactionInterface;
use Exception;
use Firebase\JWT\JWT;
use DateTimeImmutable;
use Psr\Container\ContainerInterface;
use App\Exception\ValidationException;



/**
 * Service.
 */
final class CardService
{
    /**
     * @var CardRepository
     */
    private $repository;
    private $transaction;
    private $c;
    /**
     * The constructor.
     *
     * @param CardRepository $repository The repository
     */
    public function __construct(CardRepository $repository,
                                TransactionInterface $transaction,
                                ContainerInterface $c
                                )
    {
        $this->repository = $repository;
        $this->transaction = $transaction;
        $this->c = $c;
    }
    /**
     * Create a new Card.
     *
     * @param array $data The form data
     ** @return int The new Card ID
     */
    


    public function requestCustomCard(array $data):array{
        return $this->repository->requestCustomCard($data);
    }

    public function getTheme(int $id, string $user):array{
        return $this->repository->getTheme($id, $user);
    }

    public function getThemeList():array{
        return $this->repository->getThemeList();
    }
    public function getTemplateById(int $tid): array
    {return $this->repository->getTemplateById($tid);
    }
    public function setSubscription(array $data):array{
        return $this->repository->setSubscription($data);
    }
     public function getTemplateWithToken(string $token):array{
        return $this->repository->getTemplateWithToken($token);
     }

     public function getAllCardRequest():array{
         return $this->repository->getAllCardRequest();
     }

     public function getCardList():array{
        return $this->repository->getCardList();
    }

    public function getCardListByUser(string $user_id):array{
        return $this->repository->getCardListByUser($user_id);
    }

    public function getPaidUsers(): array
    {
        return $this->repository->getPaidUsers();
    }

    public function getSubscriptionByUser(string $user_id): array
    {
        return $this->repository->getSubscriptionByUser($user_id);
    }
    public function getExpiredSubscribers(): array
    { return $this->repository->getExpiredSubscribers();
    }

    public function getActiveSubscribers(): array
    { return $this->repository->getActiveSubscribers();
    }
    public function countActiveSubscribers(): int
    { return $this->repository->countActiveSubscribers();
    }
    public function getConnectionList():array{
        return $this->repository->getConnectionList();
    }
    public function getConnectionListByUser(string $user_id):array{
        return $this->repository->getConnectionListByUser($user_id);
    }

     public function getLatestCardRequest():array{
        return $this->repository->getLatestCardRequest();
    }

     public function getAllCardRequestById(int $id):array{
        return $this->repository->getAllCardRequestById($id);
     }

     public function countAllCards(): int
     {
        return $this->repository->countAllCards();
     }
 
     public function countAllCardsByUser(string $user_id): int
     {
        return $this->repository->countAllCardsByUser($user_id);
     }
     public function countAllConnectionByUser(string $user_id): int
     {
        return $this->repository->countAllConnectionByUser($user_id);
     }
     
     public function countAllPendingCardRequest(): int
     {
        return $this->repository->countAllPendingCardRequest();
     }

     public function countAllCustomCards(): int
    {
        return $this->repository->countAllCustomCards();
    }

    public function countAllPaidCards(): int
    {
        return $this->repository->countAllPaidCards();
    }

    public function countFreeUsers(): int
    {
        return $this->repository->countFreeUsers();
    }

    public function getFreeUsers(): array
    {
        return $this->repository->getFreeUsers();
    }

    public function getCustomCardList(): array
    {
        return $this->repository->getCustomCardList();
    }
    public function createTheme(array $data): array
    {
        $data['code']=$this->getToken(12);
        return $this->repository->createTheme($data);
    }

    public function countExpiredSub(): int
    {
        return $this->repository->countExpiredSub();
    }
     function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }
}