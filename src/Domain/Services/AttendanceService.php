<?php

namespace App\Domain\Services;


use App\Domain\Repository\AttendanceRepo;
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
final class AttendanceService
{
    /**
     * @var AttendanceRepo
     */
    private $repository;
    private $transaction;
    private $c;
    /**
     * The constructor.
     *
     * @param AttendanceRepository $repository The repository
     */
    public function __construct(AttendanceRepo $repository,
                                TransactionInterface $transaction,
                                ContainerInterface $c
                                )
    {
        $this->repository = $repository;
        $this->transaction = $transaction;
        $this->c = $c;
    }
    /**
     * Create a new Attendance.
     *
     * @param array $data The form data
     ** @return int The new Attendance ID
     */
    
    public function createOrg(array $data):array{
        $data['org_id'] = $this->getToken(10);
        return $this->repository->createOrg($data);
    }

    public function createUserGroup(array $data):array{
        return $this->repository->createUserGroup($data);
    }

    public function createEvent(array $data):array{
        $data['event_id'] = $this->getToken(30);
       return  $this->repository->createEvent($data);
    }
    public function getOrg(string $user_id):array{
        return  $this->repository->getOrg($user_id);
     }
     public function getAllEvents(string $user_id):array{
        return  $this->repository->getAllEvents($user_id);
     }
     public function getUserGroup(string $user_id):array{
        return  $this->repository->getUserGroup($user_id);
     }
     public function getLatestEvents(string $user_id):array{
        return  $this->repository->getLatestEvents($user_id);
     }
    public function checkIn(array $data):array{
        return $this->repository->checkIn($data);
    }

    public function getAttendanceRange(array $data):array{
        return $this->repository->getAttendanceRange($data);
    }

     public function getTemplateWithToken(string $token):array{
        return $this->repository->getTemplateWithToken($token);
     }

     public function getAttendanceSummaryRange(array $data): array
     {
        return $this->repository->getAttendanceSummaryRange($data);
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
            $rnd = $rnd & $filter; // disAttendance irrelevant bits
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