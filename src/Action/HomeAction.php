<?php
namespace App\Action;

use App\Domain\Services\CardService;
use App\Domain\Services\AttendanceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Services\TestService;
use App\Domain\Services\UserService;
use Slim\Views\Twig;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;

final class HomeAction
{

    private $twig;
    private $cService;
    private $session;
    private $aService;
    private $uservice;

public function __construct(CardService $cService,
AttendanceService $aService,
UserService $uservice,
\Twig\Environment $twig,
SessionInterface $session)
{
$this->cService = $cService;
$this->twig = $twig;
$this->session = $session;
$this->aService = $aService;
$this->uservice = $uservice;

}

public function __invoke(
ServerRequestInterface $request,
ResponseInterface $response
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/home/index.html',
     ));
            return $response;
}

public function dashboard(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $tuser = $this->session->get('TUser');
$allRequest = $this->cService->getLatestCardRequest();
if ($tuser['usertype'] == 2){
$cons = $this->cService->countAllCards();
$cards = $this->uservice->countAllProfile();
}else{
$cards = $this->cService->countAllCardsByUser($tuser['user_id']);
$cons= $this->cService->countAllConnectionByUser($tuser['user_id']);
}
$customcard = $this->cService->countAllCustomCards();
$paidcard = $this->cService->countAllPaidCards();
$card_req = $this->cService->countAllPendingCardRequest();
$subscribed = $this->cService->countActiveSubscribers();
$alluser =$this->uservice->countAllUser();
$freeusers = $this->cService->countFreeUsers();
$expsub = $this->cService->countExpiredSub();
$countSub = $this->cService->countSubUsers($tuser['user_id']);
$atPreview = $this->aService->getAttendancePreview($tuser['user_id']);
$atRating = $this->aService->getAttendanceRating($tuser['user_id']);
$atTrends = $this->aService->getAttendanceTrends($tuser['user_id']);
$usTrends = $this->uservice->getUserTrends();
$response->getBody()->write($this->twig->render('/dashboard/home.twig',
 ['cardrequest'=>$allRequest,
 'user'=>$tuser,
 'cons'=>$cons, 'card'=>$cards,'cardreq'=>$card_req,
 'customcard'=>$customcard, 'paidcard'=>$paidcard,
 'alluser'=>$alluser, 'subscribed'=>$subscribed, 'freeuser'=>$freeusers,
 'expsub' =>$expsub,
 'inSub' => $countSub,
 'atprev' =>$atPreview,
 'atRating' =>$atRating,
 'atTrends'=>$atTrends ,
 'usTrends' =>$usTrends
]));
        return $response;
}

public function cardRequest(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/cardrequest.twig', ['user'=>$this->session->get('TUser')
    ]));
            return $response;
}

public function profileRequest(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {

    $user = $this->uservice->getUserById($args['pro_id']);
    $user_pic = $this->uservice->getUserPicByUserId($args['user_id']);
    $response->getBody()->write($this->twig->render('/home/profile.html', ['pro_user'=>$user, 'pics'=>$user_pic['profilepicture']
    ]));
            return $response;
}

public function allRequest(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $allRequest = $this->cService->getallCardRequest();
    $response->getBody()->write($this->twig->render('/dashboard/items/allpendingreq.twig',
     ['user'=>$this->session->get('TUser'),
     'cardrequest'=>$allRequest
    ]));
            return $response;
}


public function getCardLists(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $tuser = $this->session->get('TUser');
    if ($tuser['usertype'] == 2){
    $cards = $this->cService->getCardList();
    }
    else{
        $cards = $this->cService->getCardListByUser($tuser['user_id']);
    }
    $response->getBody()->write($this->twig->render('/dashboard/items/cardlist.twig',
     ['user'=>$tuser,
     'cards'=>$cards
    ]));
            return $response;
}

public function getPaidUsers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $paidusers = $this->cService->getPaidUsers();
    $response->getBody()->write($this->twig->render('/dashboard/items/paidusers.twig',
     ['user'=>$this->session->get('TUser'),
     'paidusers'=>$paidusers
    ]));
            return $response;
}

public function getAllUsers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $allUsers = $this->cService->getAllUsers();
    $response->getBody()->write($this->twig->render('/dashboard/items/allusers.twig',
     ['user'=>$this->session->get('TUser'),
     'allusers'=>$allUsers
    ]));
            return $response;
}

public function getFreeUsers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $freeusers = $this->cService->getFreeUsers();
    $response->getBody()->write($this->twig->render('/dashboard/items/freeusers.twig',
     ['user'=>$this->session->get('TUser'),
     'freeusers'=>$freeusers
    ]));
            return $response;
}

public function getCustomCardList(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $customusers = $this->cService->getCustomCardList();
    $response->getBody()->write($this->twig->render('/dashboard/items/customcards.twig',
     ['user'=>$this->session->get('TUser'),
     'customusers'=>$customusers
    ]));
            return $response;
}

public function getExpiredSubscribers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $expusers = $this->cService->getExpiredSubscribers();
    $response->getBody()->write($this->twig->render('/dashboard/items/expiredcards.twig',
     ['user'=>$this->session->get('TUser'),
     'expusers'=>$expusers
    ]));
            return $response;
}

public function getActiveSubscribers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $activesubs = $this->cService->getActiveSubscribers();
    $response->getBody()->write($this->twig->render('/dashboard/items/activesub.twig',
     ['user'=>$this->session->get('TUser'),
     'activesubs'=>$activesubs
    ]));
            return $response;
}

public function getConnectionLists(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $tuser = $this->session->get('TUser');
    if ($tuser['usertype'] == 2){
    $cons = $this->cService->getConnectionList();
    }else{
    $cons = $this->cService->getConnectionListByUser($tuser['user_id']);
    }
    $response->getBody()->write($this->twig->render('/dashboard/items/connections.twig',
     ['user'=>$this->session->get('TUser'),
     'cons'=>$cons
    ]));
    return $response;
}

public function getSubUsers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $tuser = $this->session->get('TUser');
    $subUsers = $this->cService->getSubUsers($tuser['user_id']);
    $response->getBody()->write($this->twig->render('/dashboard/items/inSub.twig',
     ['user'=>$this->session->get('TUser'),
     'subUsers'=>$subUsers
    ]));
    return $response;
}

public function deleteSubUsers(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $pid = $args['id'];
    $tuser = $this->session->get('TUser');
    $this->cService->deleteSubUsers($pid);
    $subUsers = $this->cService->getSubUsers($tuser['user_id']);
    $response->getBody()->write($this->twig->render('/dashboard/items/inSub.twig',
     ['user'=>$this->session->get('TUser'),
     'subUsers'=>$subUsers
    ]));
    return $response;
}
public function login(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/login-v2.html', [
    ]));
            return $response;
}


public function teekonectInfo(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/home/teekonect.html', [
    ]));
            return $response;
}


public function privacy(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/privacy.twig', [
    ]));
            return $response;
}
public function faq(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/faq.html', [
    ]));
            return $response;
}

public function confirmAccount(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $userid = $args['userid'];
    $result = $this->uservice->enableUserByUserID($userid);
    $response->getBody()->write($this->twig->render('/dashboard/confirmation.twig', ['result'=>$result
    ]));
            return $response;
}

public function getQR(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $event = $args['event'];
    $org = $args['org_id'];
    $response->getBody()->write($this->twig->render('/attendance/qrAttendance.twig', ['event'=>$event, 'org'=>$org
    ]));
            return $response;
}
public function logout(
    ServerRequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
   
    if ($this->session->get('TUser')) {
        $this->session->destroy();
    }
    $response->getBody()->write($this->twig->render('/dashboard/login-v2.html', [
    ]));
            return $response;
}


public function register(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/register-v2.html', [
    ]));
            return $response;
}

public function accountSearch(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/searchaccount.html', [
    ]));
            return $response;
}

public function deleteAccount(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/deleteaccount.html', [
    ]));
            return $response;
}

public function recoverPassword(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/recover-password-v2.html', [
    ]));
            return $response;
}

public function forgotPassword(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/dashboard/forgot-password-v2.html', [
    ]));
            return $response;
}


public function organization(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $result = $this->aService->getOrg($this->session->get('TUser')['user_id']);
    $latestEvents = $this->aService->getLatestEvents($this->session->get('TUser')['user_id']);
    $response->getBody()->write($this->twig->render('/attendance/organization.twig', ['orgs'=>$result, 'events'=>$latestEvents,
    'user'=>$this->session->get('TUser')
    ]));
            return $response;
}

public function eventDetails(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/attendance/event_details.twig', ['user'=>$this->session->get('TUser')
    ]));
            return $response;
}
public function attendance(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $response->getBody()->write($this->twig->render('/attendance/attendance.twig', ['user'=>$this->session->get('TUser')
    ]));
            return $response;
}

public function events(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $events = $this->aService->getAllEvents($this->session->get('TUser')['user_id']);
    $response->getBody()->write($this->twig->render('/attendance/event.twig', ['user'=>$this->session->get('TUser'), 'events'=>$events
    ]));
            return $response;
}

public function create_Ugroup(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $usergroup = $this->aService->getUserGroup($this->session->get('TUser')['user_id']);
    $response->getBody()->write($this->twig->render('/attendance/usergroup.twig', ['user'=>$this->session->get('TUser'), 'usergroup'=>$usergroup
    ]));
            return $response;
}

public function createTheme(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $req_id = isset($args['req_id'])? $args['req_id']:null;
    $response->getBody()->write($this->twig->render('/cardthemes/customCard.twig', ['user'=>$this->session->get('TUser'), 
    'req_id' =>$req_id
    ]));
            return $response;
}

public function createEvent(
    ServerRequestInterface $request,
    ResponseInterface $response, array $args
): ResponseInterface {
    $result = $this->aService->getOrg($this->session->get('TUser')['user_id']);
    $latestEvents = $this->aService->getLatestEvents($this->session->get('TUser')['user_id']);
    $response->getBody()->write($this->twig->render('/attendance/create_event.twig', ['orgs'=>$result, 'events'=>$latestEvents,
    'user'=>$this->session->get('TUser')
    ]));
            return $response;
}
}
