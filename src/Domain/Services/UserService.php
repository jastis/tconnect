<?php

namespace App\Domain\Services;


use App\Domain\Repository\UserRepository;
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
final class UserService
{
    /**
     * @var UserServicesRepository
     */
    private $repository;
    private $transaction;
    private $c;
    /**
     * The constructor.
     *
     * @param UserServicesRepository $repository The repository
     */
    public function __construct(UserRepository $repository,
                                TransactionInterface $transaction,
                                ContainerInterface $c
                                )
    {
        $this->repository = $repository;
        $this->transaction = $transaction;
        $this->c = $c;
    }
    /**
     * Create a new user.
     *
     * @param array $data The form data
     ** @return int The new user ID
     */
    public function createNewUser(array $req_data): array
    {
        $this->transaction->begin();
        $result = [];
        $data =[];
        $error=[];
        
             $req_data['userid'] = $this->getToken(30);
             $result['data']= $this->repository->CreateNewUser("usertbl",$req_data);
             if($result['data']['id']>0){
              $result['vlink'] = $req_data['userid'];
             $result['Code'] =200;
             $this->transaction->commit();
             }
             else{
                $result['Code'] = 400;
                $result['error']['type'] ='Bad Request';
                $result['error']['description'] =$result['data']['description'];  
             }
           
        return $result;
        
    }
    public function createUser(array $req_data): array
    {
        $this->transaction->begin();
        $result = [];
        $data =[];
        $error=[];
        try {
             $req_data['userid'] = $this->getToken(30);
             $result['data']= $this->repository->CreateUser("profile",$req_data);
             if($result['data']['id']>0){
             $result['Code'] =200;
             $this->transaction->commit();
             }
             else{
                $result['Code'] = 400;
                $result['error']['type'] ='Bad Request';
                $result['error']['description'] =$result['data']['description'];  
             }
            } catch (\Throwable $th) {
                // Revert all changes
                $this->transaction->rollback();
                $result['Code'] = 500;
                $result['error']['type'] ='Internal Server Error';
                $result['error']['description'] = $th; //'Registration failed. Contact Administrator';//"Unable to submit new information!";
            }
        return $result;
        
    }
    public function getProfile(string $user_id):array{
    return $this->repository->getProfile($user_id);
    }
    public function removeProfile(string $user_id, int $cid):array{
        return $this->repository->removeProfile($user_id,  $cid);
        }

        public function removeUserByUserID(string $user_id):array{
            return $this->repository->removeUserByUserID($user_id);
            }
    
        

        public function removeCard(array $data):array{
            return $this->repository->removeCard($data);
            }
    
    public function authenticateUser(array $userData):array
    {
            $result=[];
            $this->checkUserData($userData);
            $response = $this->repository->getUser($userData);
            $hasValidCredentials = $response['found'];
            if ($hasValidCredentials)
            {
            $token = $this->generateToken($userData['email']);
            $result['data']= $response['user'];
            $result['statusCode'] =200;
            $result['token'] =$token;
            }
            else
            {
            $result['statusCode'] = 401;
            $result['error']['type'] =$response['message'];
            $result['error']['Description'] = $response['message'];
            }
       return $result;
    }
    public function authenticateAppUser(array $userData):array
    {
            $result=[];
            $this->checkUserData($userData);
            $response = $this->repository->getAppUser($userData);
            $hasValidCredentials = $response['found'];
            if ($hasValidCredentials)
            {
            $token = $this->generateToken($userData['email']);
            $result['data']= $response['user'];
            $result['data']['password'] =$userData['password'];
            $result['statusCode'] =200;
            $result['data']['token'] =$token;
            $loginAt= new DateTimeImmutable();
            $result['data']['loginAt']  = $loginAt->getTimestamp();  
            $result['data']['expireAt'] = $loginAt->modify('+5 years')->getTimestamp();      // Add 5years
            }
            else
            {
            $result['statusCode'] = 401;
            $result['error']['type'] =$response['message'];
            $result['error']['Description'] = $response['message'];
            }
        return $result;
    }
   
    public function updateProfilePicture(array $data):array{
        return $this->repository->updateProfilePicture($data);
        }
        
    public function generateToken(string $username): string
    {
        $secretKey  = $this->c->get('settings')['security']['secretKey'];
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+5 years')->getTimestamp();      // Add 5years
        $serverName = $this->c->get('settings')['security']['serverName'];
                                                

        $data = [
            'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
            'iss'  => $serverName,                       // Issuer
            'nbf'  => $issuedAt->getTimestamp(),         // Not before
            'exp'  => $expire,                           // Expire
            'userName' => $username,                     // User name
        ];
        return JWT::encode(
            $data,
            $secretKey,
            'HS512'
        );
    }

    public function ValidateHeader(string $jwt_token): bool
    {
        try {
            $respose=[];
            if (! preg_match('/Bearer\s(\S+)/', $jwt_token, $matches)) {
            //    $respose['header']= 400;
            //    $respose['message']='Token not found in request';
              return false;
            }
            $jwt = $matches[1];
            if (! $jwt) {
            //    $respose['header']= 400;
            //    $respose['message']='Token not found in request';
              return false;
            }
            $secretKey  =  $this->c->get('settings')['security']['secretKey'];
            $token = JWT::decode($jwt, $secretKey, ['HS512']);
            $now = new DateTimeImmutable();
            $serverName =  $this->c->get('settings')['security']['serverName'];
            
            if ($token->iss !== $serverName ||
                $token->nbf > $now->getTimestamp() ||
                $token->exp < $now->getTimestamp())
            {
                // $respose['header']= 401;
                // $respose['message']='Unauthorized Access!';
                return false;
            }
            else {
                // $respose['header']= 200;
                // $respose['message']='Access Granted';
                return true;
            }
    
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function validateNewUser(array $data): void
    {
        $errors = [];

        // Here you can also use your preferred validation library

        if (empty($data['username'])) {
            $errors['username'] = 'Input required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Input required';
        } elseif (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Invalid email address';
        }

        if ($errors) {
            throw new ValidationException('Please check your input', $errors);
        }
    }
    public function checkUserData(array $data): void
    {
        $errors = [];

        // Here you can also use your preferred validation library

        if (empty($data['password'])) {
            $errors['password'] = 'Input required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Input required';
        } elseif (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Invalid email address';
        }

        if ($errors) {
            throw new ValidationException('Please check your input', $errors);
        }
    }
    
    // private function cleanData(array $data):array
    // {
        
    // }

    public function getAllCountries():array{
        $result = $this->repository->getAllCountries();
        return $result;
    }
    
    public function getAllStates(int $country):array{
        $result = $this->repository->getAllStates($country);
        return $result;
    }
    public function getAllCities(int $state):array{
        $result = $this->repository->getAllCities($state);
        return $result;
    }
    
    public function getUserByUserId(string $user_id): array
    { return $this->repository->getUserByUserId($user_id);
    }

    public function getUserPicByUserId(string $user_id): array
    { return $this->repository->getUserPicByUserId($user_id);
    }

    public function getUserById(int $id):array{
        return $this->repository->getUserById($id);
    }

    public function getActiveUsers():array{
        return $this->repository->getActiveUsers();
    }

    public function getAccountStatus(string $email):int {
        return $this->repository->getAccountStatus($email);
    }

    public function enableUser(int $user_id):void{
         $this->repository->enableUser($user_id);
    }
    public function enableUserByUserID(string $userid):array{
      return  $this->repository->enableUserByUserID($userid);
   }

    public function disableUser(int $user_id):void{
        $this->repository->disableUser($user_id);
    }

    public function getInActiveUsers():array{
        return $this->repository->getInActiveUsers();
    }

    public function updateUserProfile(array $data):array{
        return $this->repository->updateUserProfile($data);
    }

    public function addProfile(array $data):array{
        return $this->repository->addProfile($data);
    }

    public function getUserTrends():array{
        return $this->repository->getUserTrends();
    }

    public function editProfile(array $data):array{
        return $this->repository->editProfile($data);
    }

    public function countAllProfile(): int
    {
        return $this->repository->countAllProfile();
    }


    public function countAllProfileByUser(string $user_id): int
    {
        
        return $this->repository->countAllProfileByUser($user_id);
    }

    public function countAllUser(): int
    {
        return $this->repository->countAllUser();
    }
    public function countSubscribed(): int
    {
        return $this->repository->countSubscribed();
    }
    public function addOtherProfile(array $data, string $uid):array{
        return $this->repository->addOtherProfile($data, $uid);
    }
    public function updatePasswordById(array $data):array{
       return $this->repository->updatePasswordById($data);
    }
    
    public function resetPasswordByMail(array $data):array{
        return $this->repository->resetPasswordByMail($data);
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