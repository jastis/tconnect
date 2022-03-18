<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\Services\AttendanceService;
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




class AttendanceAction
{

    private $uservices;
    public $session;
    private $c;
    private $brender;
    private $mailer;
    private $twig;
    private $aservices;

    public function __construct(

        \Twig\Environment $twig,
        ContainerInterface $c,
        AttendanceService $aservices,
        BodyRendererInterface $brender,
        MailerInterface $mailer,
        SessionInterface $session
    ) {


        $this->twig = $twig;
        $this->aservices = $aservices;
        $this->c = $c;
        $this->brender = $brender;
        $this->mailer = $mailer;
        $this->session = $session;
    }



    public function addOrganization(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $uri = $request->getUri();
        $data['user_id'] = $this->session->get('TUser')['user_id'];
        $settings = $this->c->get('settings');
        $directory = $settings['assets']['logopath'];
        $uploadedFiles = $request->getUploadedFiles();

        if (isset($uploadedFiles['logo'])) {
            $uploadedFile = $uploadedFiles['logo'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $data['logo'] = $uri->getScheme() . '://' . $uri->getHost() . '/upload/logo/' . $filename;
            } else {
                $data['logo'] = null;
            }
        }

        $result = $this->aservices->createOrg($data);

        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($result['Code']);
    }

    public function addEvent(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getParsedBody();
        $data['user_id'] = $this->session->get('TUser')['user_id'];

        $result = $this->aservices->createEvent($data);

        $response->getBody()->write((string) json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($result['Code']);
    }

    

    public function getAttendanceRange(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getQueryParams();
        $orgs = $this->aservices->getOrg($this->session->get('TUser')['user_id']);
        $result = $this->aservices->getAttendanceRange($data);
        $events = $this->aservices->getAllEvents($this->session->get('TUser')['user_id']);
        $response->getBody()->write($this->twig->render('/attendance/attendance.twig', [
            'result' => $result, 'orgs' => $orgs, 'events' => $events,
            'user' => $this->session->get('TUser')
        ]));
        return $response;
    }

    public function getAttendanceSummaryRange(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getQueryParams();
        $orgs = $this->aservices->getOrg($this->session->get('TUser')['user_id']);
        $result = $this->aservices->getAttendanceSummaryRange($data);
        $events = $this->aservices->getAllEvents($this->session->get('TUser')['user_id']);
        $response->getBody()->write($this->twig->render('/attendance/attendance_summary.twig', [
            'result' => $result, 'orgs' => $orgs, 'events' => $events,
            'user' => $this->session->get('TUser')
        ]));
        return $response;
    }

    public function getAttendanceSummary(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array) $request->getQueryParams();
        $orgs = $this->aservices->getOrg($this->session->get('TUser')['user_id']);
        $result = $this->aservices->getAttendanceRange($data);
        $events = $this->aservices->getAllEvents($this->session->get('TUser')['user_id']);
        $response->getBody()->write($this->twig->render('/attendance/attendance_summary.twig', [
            'result' => $result, 'orgs' => $orgs, 'events' => $events,
            'user' => $this->session->get('TUser')
        ]));
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
