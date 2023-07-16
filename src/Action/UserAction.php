<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\Services\UserService;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Slim\Psr7\Response;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use \Twig\Environment;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Slim\Psr7\UploadedFile;




class UserAction
{

    private $uservices;
    public $session;
    private $c;
    private $brender;
    private $mailer;
    private $twig;

    public function __construct(
        SessionInterface $session,
        \Twig\Environment $twig,
        ContainerInterface $c,
        UserService $uservices,
        BodyRendererInterface $brender,
        MailerInterface $mailer

    ) {


        $this->twig;
        $this->uservices = $uservices;
        $this->c = $c;
        $this->brender = $brender;
        $this->mailer = $mailer;
        $this->session = $session;
    }

    public function registerNewUser(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        $uri = $request->getUri();
        $data = (array) $request->getParsedBody();
        $settings = $this->c->get('settings');
        $directory = $settings['assets']['photopath'];
        $uploadedFiles = $request->getUploadedFiles();
        // handle single input with single file upload
        if (isset($uploadedFiles['photo'])) {
            $uploadedFile = $uploadedFiles['photo'];

            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $data['photo'] = $filename;
            } else {
                $data['photo'] = null;
            }
        }
        $directory = $settings['assets']['logopath'];
        if (isset($uploadedFiles['logo'])) {
            $uploadedFile = $uploadedFiles['logo'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $data['logo'] = $filename;
            } else {
                $data['logo'] = null;
            }
        }
        $result = $this->uservices->createNewUser($data);
        $result['vlink'] = $uri->getScheme() . '://' . $uri->getHost() .'/user/activate/'.$result['vlink'];
        if ((int) $result['data']['id'] > 0) {
           $smsSent = $this->SendSMS('Thank you, use this link to verify your Teekonect account. '.$result['vlink'] , ('%2B'.$data['phone_no']));//
           $result['smsStatus'] = $smsSent; 
           $email = (new TemplatedEmail())
                ->from('donotreply@briisi.com')
                ->to($data['email'])
                ->subject('Welcome to Teekonect')

                // path of the Twig template to render
                ->htmlTemplate('/emails/welcomeMail.twig')

                // pass variables (name => value) to the template
                ->context([
                    'name' => $data['last_name'], 'veri_link' => $result['vlink']
                ]);
            // Render the email twig template
            $this->brender->render($email);
            // Send email
            try {
                $this->mailer->send($email);
            } catch (\Throwable $th) {
                $result['description'] = $th;
            }
        }

        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($result['Code']);
    }

    public function addProfile(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        try {
            $uri = $request->getUri();
            $data = (array) $request->getParsedBody();
            $settings = $this->c->get('settings');
            $directory = $settings['assets']['logopath'];
            $uploadedFiles = $request->getUploadedFiles();
            $item = json_decode($data['item'], true);
            $item["user_id"] = $data['uid'];
            if (isset($uploadedFiles['logo'])) {
                $uploadedFile = $uploadedFiles['logo'];
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = $this->moveUploadedFile($directory, $uploadedFile);
                    $item['logo'] = $uri->getScheme() . '://' . $uri->getHost() . '/upload/logo/' . $filename;
                } else {
                    $item['logo'] = null;
                }
            }
            $result = $this->uservices->addProfile($item);
            if($result){
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            }else{
                $result['error']['type'] = 'Server Error';
                $result['error']['Description'] = "Sorry, you can only create one digi-me from a template. To create a new template, kindly delete the old one and re-create.";
                $response->getBody()->write((string) json_encode($result));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
            }
        } catch (\Throwable $th) {
            $result['error']['type'] = 'Bad Request';
            $result['error']['Description'] = "Card not saved!, Fill out all required information";
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }


    public function editProfile(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        try {
            $uri = $request->getUri();
            $data = (array) $request->getParsedBody();
            $settings = $this->c->get('settings');
            $directory = $settings['assets']['logopath'];
            $uploadedFiles = $request->getUploadedFiles();
            $item = json_decode($data['item'], true);
            $item["user_id"] = $data['uid'];
            if (isset($uploadedFiles['logo'])) {
                $uploadedFile = $uploadedFiles['logo'];
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = $this->moveUploadedFile($directory, $uploadedFile);
                    $item['logo'] = $uri->getScheme() . '://' . $uri->getHost() . '/upload/logo/' . $filename;
                } else {
                    $item['logo'] = null;
                }
            }
            $result = $this->uservices->editProfile($item);
            if($result){
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            }else{
                $result['error']['type'] = 'Server Error';
                $result['error']['Description'] = "Error updating Card";
                $response->getBody()->write((string) json_encode($result));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
            }
        } catch (\Throwable $th) {
            $result['error']['type'] = 'Bad Request';
            $result['error']['Description'] = "Card not Updated!";
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    public function updateProfilePicture(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        try {
            $item = [];
            $item['user_id'] = $args['user_id'];
            $uri = $request->getUri();
            // $data = (array) $request->getParsedBody(); 
            $settings = $this->c->get('settings');
            $directory = $settings['assets']['photopath'];
            $uploadedFiles = $request->getUploadedFiles();

            if (isset($uploadedFiles['profilePic'])) {
                $uploadedFile = $uploadedFiles['profilePic'];
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = $this->moveUploadedFile($directory, $uploadedFile);
                    $item['photo'] = $uri->getScheme() . '://' . $uri->getHost() . '/upload/photo/' . $filename;
                } else {
                    $item['photo'] = null;
                }
            }
            $result = $this->uservices->updateProfilePicture($item);
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Throwable $th) {
            $result['statusCode'] = 500;
            $result['error']['type'] = 'Bad Request';
            $result['error']['Description'] = "Profile Picture not updated!";
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    public function addOtherProfile(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        try {
            
            $data = (array) $request->getParsedBody();
            $item = explode('/', $data['item']);
            $result = $this->uservices->addOtherProfile($item, $data['uid']);
            if ($result['subscribe'] === true){
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            } else{
        
            $result['error']['type'] = 'Server Error';
            $result['error']['Description'] = "Sharing Failed!  Subscription is pending on this card";
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
            }


        } catch (\Throwable $th) {
            $result['error']['type'] = 'Bad Request';
            $result['error']['Description'] = "Card not found";
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    public function enableUserByUserID(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $userid = $args['userid'];
        $result = $this->uservices->enableUserByUserID($userid);
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function removeUserByUserID(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $result = $this->uservices->removeUserByUserID($this->session->get('TUser')['user_id']);
        $response->getBody()->write((string) json_encode($result));
        if ($this->session->get('TUser')) {
            $this->session->destroy();
        }
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function getAccountStatus(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getQueryParams();
        $result = $this->uservices->getAccountStatus($data['email']);
        $result = $result === 1? "Account Active": ($result === 0?"Account Not Active": "Account Not Found");
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }


    public function getProfile(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        $data = (array) $request->getParsedBody();
        $result = $this->uservices->getProfile($data['uid']);
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function removeProfile(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        $data = (array) $request->getParsedBody();
        $result = $this->uservices->removeProfile($data['uid'], (int)$data['cid']);
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function removeCard(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        // $this->logger->info(sprintf('User created:'));
        $data = (array) $request->getParsedBody();
        $result = $this->uservices->removeCard($data);
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function updateUserProfile(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $data['userid'] = (int) $this->session->get('TUser')['id'];
        $result = $this->uservices->updateUserProfile($data);
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function updatePasswordById(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $data['userid'] = (isset($data['id'])) ? $data['id'] : (int) $this->session->get('TUser')['id'];
        $result = $this->uservices->updatePasswordById($data);
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function resetPasswordByMail(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $result = $this->uservices->resetPasswordByMail($data);
        if ($result['error']) {
            $response->getBody()->write((string) json_encode($result));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
        $data['first_name'] = $result['first_name'];
        $data['code'] = $result['newPass'];
        $email = (new TemplatedEmail())
            ->from('donotreply@briisi.com')
            ->to($data['email'])
            ->subject('Password Reset')

            // path of the Twig template to render
            ->htmlTemplate('/emails/passwordreset.twig')

            // pass variables (name => value) to the template
            ->context([
                'data' => $data,
            ]);
        // Render the email twig template
        $this->brender->render($email);
        // Send email
        try {
            $this->mailer->send($email);
        } catch (\Throwable $th) {
            $result['description'] = 'Unable to Send Password Reset Mail';
        }


        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }



    public function loginUser(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $result = $this->uservices->authenticateUser($data);
        if (isset($result['data']['id'])) {
            $this->session->destroy();
            $this->session->start();
            $this->session->regenerateId();
            $this->session->set('TUser', $result['data']);
        }
        //$result = $this->uservices->ValidateHeader(implode(" ",$request->getHeader('Authorization')));
        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($result['statusCode']);
    }

    

    public function appLogin(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $result = $this->uservices->authenticateAppUser($data);
        $response->getBody()->write((string) json_encode(($result['data']?$result['data']:$result), JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($result['statusCode']);
    }

    public function SendSMS(string $msg = 'testing from app', $to): string {
        $settings = $this->c->get('settings');
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/AC2c5791d5bf5e1ff3e207fc1fce9fcb7c/Messages.json',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'To='.$to.'&MessagingServiceSid='.$settings['sms']['msid'].'&Body='.$msg.'&From=%2B16109956792',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$settings['sms']['token'],
            'Content-Type: application/x-www-form-urlencoded'
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
      return $response;
    }

    
    public function moveUploadedFile($directory, UploadedFile $uploadedFile): string
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}
