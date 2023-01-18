<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\Services\CardService;
use App\Domain\Services\AttendanceService;
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




class CardAction
{
   
    private $uservices;
    public $session;
    private $c;
    private $brender;
    private $mailer;
    private $twig;
    private $aservice;
    private $cservices;

    public function __construct(
       
        \Twig\Environment $twig,
        ContainerInterface $c,
         CardService $cservices,
         AttendanceService $aservice,
         UserService $uservices,
         BodyRendererInterface $brender,
         MailerInterface $mailer
    ) {
      
     
       $this->twig;
        $this->cservices= $cservices;
        $this->c=$c;
        $this->brender=$brender;
        $this->mailer= $mailer;
        $this->aservice = $aservice;
        $this->uservices = $uservices;
    }


   private  function encrypt3Des($data, $key){

        $encData = openssl_encrypt($data, 'DES-EDE3', $key, OPENSSL_RAW_DATA);
        
          return base64_encode($encData); 
      
       }

    public function requestCustomCard (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        // $result = $this->encrypt3Des((string) json_encode($data),'api key');
       
        $settings = $this->c->get('settings');
        $directory = $settings['assets']['cardrequestpath'];
        // $cardPrice = $settings['cost']['customCard'];
         $currency = $settings['cost']['currency'];
        // $data['amount']= $cardPrice * floatval($data['cardsize']);
        $uploadedFiles = $request->getUploadedFiles();
       
         if (isset($uploadedFiles['front'])){
            $uploadedFile = $uploadedFiles['front'];
        
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $data['front'] = $filename;
        }else{
            $data['front'] = null;
        }
        }
        if (isset($uploadedFiles['back'])){
        $uploadedFile = $uploadedFiles['back'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $data['back'] = $filename;
        }else{
            $data['back'] = null;
        }
    }
    $result =$this->cservices->requestCustomCard($data);
    if ($result['Code'] === 200){
        $this->SendSMS('Thank you, Your custom template request with refID-'. $data['tx_ref'].' successful. We will notify you when the design is ready. Teekonect'   , ('%2B'.$data['phone']));
        $email = (new TemplatedEmail())
                    ->from('info@briisi.com')
                    ->to($data['email'])
                    ->subject('Request Confirmation')

                    // path of the Twig template to render
                    ->htmlTemplate('/emails/cardRequest.twig')

                    // pass variables (name => value) to the template
                    ->context([
                        'r_name' => $data['name'], 'r_email'=>$data['email'],
                         'r_phone'=>$data['phone'], 'card_limit'=>$data['cardsize'],
                         'amount'=>$data['total'], 'currency'=>$currency
                         ]);
                    // Render the email twig template
                    $this->brender->render($email);
                    // Send email
                    // try {
                         $this->mailer->send($email);
                     
                    // } catch (\Throwable $th) {
                    //     $result['description'] =$th;
                    // } 
                }
    
    $response->getBody()->write((string) json_encode($result));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getTemplateWithToken (ServerRequestInterface $request,
    ResponseInterface $response, array $args ): ResponseInterface {
        $token = $args['token'];
        $result =$this->cservices->getTemplateWithToken($token);
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function mailSender (ServerRequestInterface $request,
    ResponseInterface $response, array $args ): ResponseInterface {
        
        $user = $this->uservices->getUserByUserId($args['user_id']);
        $args['email'] = $user['email'];
        $args['first_name'] = $user['first_name'];
        $result=$this->sendReminderMail($args);
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }


    public function createTheme(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        
        $data = (array) $request->getParsedBody();
        $settings = $this->c->get('settings');
        $result = $this->cservices->createTheme($data);
        // if ((int) $result['data']['id'] > 0) {
        //     $email = (new TemplatedEmail())
        //         ->from('donotreply@briisi.com')
        //         ->to($data['email'])
        //         ->subject('Welcome to Teekonect')
        //         // path of the Twig template to render
        //         ->htmlTemplate('/emails/welcomeMail.twig')
        //         // pass variables (name => value) to the template
        //         ->context([
        //             'name' => $data['last_name'], 'veri_link' => $result['vlink']
        //         ]);
        //     // Render the email twig template
        //     $this->brender->render($email);
        //     // Send email
        //     try {
        //         $this->mailer->send($email);
        //     } catch (\Throwable $th) {
        //         $result['description'] = $th;
        //     }
        // }

        $response->getBody()->write((string) json_encode($result));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($result['code']);
    }

    public function sendReminderMail(
       array $args
    ): int {
        $result =0;
            $email = (new TemplatedEmail())
                ->from('donotreply@briisi.com')
                ->to($args['email'])
                ->subject('Pending Subscription Renewal')
                // path of the Twig template to render
                ->htmlTemplate('/emails/reminderMail.twig')
                // pass variables (name => value) to the template
                ->context([
                    'data' => $args
                ]);
            // Render the email twig template
            $this->brender->render($email);
            // Send email
            try {
                $this->mailer->send($email);
                $result= 1;
            } catch (\Throwable $th) {
                $result = 0;
            }
        
        return $result;
    }

    public function getCardPrice (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $settings = $this->c->get('settings');
        $result =$settings['cost'];
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
    public function getTemplatePrice (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $settings = $this->c->get('settings');
        $result =$settings['cost'];
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getCardTheme (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $data = (array) $request->getParsedBody(); 
        $result =$this->cservices->getTheme((int)$data['id'], $data['user']);
        $result['theme'] =  $result['theme']? json_decode($result['theme'],true,JSON_UNESCAPED_SLASHES):null;
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
    
    public function setSubscription (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $data = (array) $request->getParsedBody(); 
    //     $settings = $this->c->get('settings');
    //     $cardPrice = $settings['cost']['template'];
    //    $data['cost'] = $cardPrice ;     
    $template = $this->cservices->getTemplateById((int) $data['template']);
    $user = $this->uservices->getUserByUserId($data['user']);
        $result =$this->cservices->setSubscription($data);
        $result['phone']= '%2B'.$user['phone_no'];
        $this->SendSMS('Thank you, Your Subscription for template ['. $template['name'].'] was successful. Ref ID- '.
        $data['tx_ref'].'. Create and share your card with people on Teekonet.'   , ('%2B'.$user['phone_no']));
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } 

    public function getThemeList (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $result =$this->cservices->getThemeList();
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } 

    public function getSubscriptionByUser (ServerRequestInterface $request,
    ResponseInterface $response ): ResponseInterface {
        $data = (array) $request->getParsedBody(); 
        $result =$this->cservices->getSubscriptionByUser($data['user_id']);
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } 

    public function checkIncheckOut(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $result = $this->aservice->checkIn($data);
        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($result['Code']);
    }

       public function moveUploadedFile($directory, UploadedFile $uploadedFile): string
            {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8)); 
            $filename = sprintf('%s.%0.8s', $basename, $extension);
        
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        
            return $filename;
            }  
            
            public function SendSMS(string $msg , $to): void {
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
              
            }

        }