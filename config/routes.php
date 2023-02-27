<?php
use App\Action\UserAction;
use App\Action\CardAction;
use App\Action\AttendanceAction;
use App\Action\HomeAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Middleware\UserAuthMiddleware;
use App\Middleware\AuthHeaderMiddleware;

use Slim\App;
return function (App $app) {
    $app->get('/', \App\Action\HomeAction::class)->setName('home'); 
    $app->get('/teekonect', \App\Action\HomeAction::class.':dashboard')->add(UserAuthMiddleware::class)->setName('dashboard'); 
    $app->get('/login', \App\Action\HomeAction::class.':login')->setName('login');
    $app->get('/logout', \App\Action\HomeAction::class.':logout')->setName('logout');
    $app->get('/register', \App\Action\HomeAction::class.':register')->setName('register');
    $app->get('/recoverpassword', \App\Action\HomeAction::class.':recoverPassword')->setName('recoverPassword');
    $app->get('/forgotpassword', \App\Action\HomeAction::class.':forgotPassword')->setName('forgotPassword');
    $app->get('/organization', \App\Action\HomeAction::class.':organization')->add(UserAuthMiddleware::class)->setName('organization');
    $app->get('/event_details', \App\Action\HomeAction::class.':eventDetails')->add(UserAuthMiddleware::class)->setName('event_details');
    $app->get('/attendance', \App\Action\HomeAction::class.':attendance')->add(UserAuthMiddleware::class)->setName('attendance');
    $app->get('/events', \App\Action\HomeAction::class.':events')->add(UserAuthMiddleware::class)->setName('events');
    $app->get('/events/create', \App\Action\HomeAction::class.':createEvent')->add(UserAuthMiddleware::class)->setName('create_event');
    $app->get('/events/usergroup', \App\Action\HomeAction::class.':create_Ugroup')->add(UserAuthMiddleware::class)->setName('create_usergroup');
    $app->get('/newtheme', \App\Action\HomeAction::class.':createTheme')->add(UserAuthMiddleware::class)->setName('createTheme');
    $app->get('/newtheme/{req_id}', \App\Action\HomeAction::class.':createTheme')->add(UserAuthMiddleware::class);
    $app->get('/sendmail/{user_id}/{cardname}', \App\Action\CardAction::class.':mailSender')->add(UserAuthMiddleware::class);
    $app->get('/privacy', \App\Action\HomeAction::class.':privacy');
    $app->get('/cardrequests', \App\Action\HomeAction::class .':cardRequest')->add(UserAuthMiddleware::class)->setName('cardRequest');
 
    $app->group('/user', function (Group $group) {
        $group->POST('/create', UserAction::class .':registerNewUser');
        $group->POST('/login', UserAction::class .':appLogin');
        $group->POST('/weblogin', UserAction::class .':loginUser');
        $group->POST('/editUser', UserAction::class .':updateUserProfile');
        $group->POST('/photo/{user_id}', UserAction::class .':updateProfilePicture');
        $group->GET('/activate/{userid}', HomeAction::class .':confirmAccount');  
        $group->POST('/forgot_pw', UserAction::class .':resetPasswordByMail'); 
        $group->POST('/reset_pw', UserAction::class .':updatePasswordById'); 

    });

    $app->group('/profile', function (Group $group) {
        $group->POST('', UserAction::class .':getProfile')->add(AuthHeaderMiddleware::class);
        $group->POST('/add', UserAction::class .':addProfile')->add(AuthHeaderMiddleware::class);
        $group->POST('/edit', UserAction::class .':editProfile')->add(AuthHeaderMiddleware::class);
        $group->POST('/add/other', UserAction::class .':addOtherProfile')->add(AuthHeaderMiddleware::class);
        $group->POST('/remove', UserAction::class .':removeProfile')->add(AuthHeaderMiddleware::class);
        $group->POST('/removeother', UserAction::class .':removeCard')->add(AuthHeaderMiddleware::class);
    });

    $app->group('/attendance', function (Group $group) {
        $group->POST('', AttendanceAction::class .':getProfile');
        $group->POST('/add/organization', AttendanceAction::class .':addOrganization')->add(UserAuthMiddleware::class);
        $group->POST('/add/usergroup', AttendanceAction::class .':addUserGroup')->add(UserAuthMiddleware::class);
        $group->POST('/add/event', AttendanceAction::class .':addEvent')->add(UserAuthMiddleware::class);
        $group->POST('/add/check', CardAction::class .':checkIncheckOut');
        $group->POST('/{organization}/{start}/{end}', AttendanceAction::class .':getAttendanceByOrg')->add(UserAuthMiddleware::class);
        $group->GET('/report/all', AttendanceAction::class .':getAttendanceRange')->add(UserAuthMiddleware::class);
        $group->GET('/report/summary', AttendanceAction::class .':getAttendanceSummaryRange')->add(UserAuthMiddleware::class);
        $group->GET('/qr/{org_id}/{event}',HomeAction::class .':getQR')->add(UserAuthMiddleware::class);
        
    });


    $app->group('/customcard', function (Group $group) {
        $group->POST('', CardAction::class .':getCustomCards');
        $group->GET('/use/{token}', CardAction::class .':getTemplateWithToken');
        $group->GET('/price', CardAction::class .':getCardPrice');
        $group->POST('/add', CardAction::class .':createTheme');
        $group->POST('/request', CardAction::class .':requestCustomCard')->add(AuthHeaderMiddleware::class);
        $group->POST('/remove', CardAction::class .':removeCustomCard');

    });

    $app->group('/card', function (Group $group) {
        $group->POST('', CardAction::class .':getCards');
        $group->POST('/theme', CardAction::class .':getCardTheme')->add(AuthHeaderMiddleware::class);
        $group->GET('/themelist', CardAction::class .':getThemeList')->add(AuthHeaderMiddleware::class);
        $group->GET('/price', CardAction::class .':getTemplatePrice');
    });

    $app->group('/subscription', function (Group $group) {
        $group->POST('', CardAction::class .':getSubscriptions');
        $group->POST('/request', CardAction::class .':setSubscription')->add(AuthHeaderMiddleware::class);
        $group->POST('/user', CardAction::class .':getSubscriptionByUser')->add(AuthHeaderMiddleware::class);
    });
 $app->group('/dashboard', function (Group $group) {
        $group->GET('/allrequest', HomeAction::class .':allRequest')->add(UserAuthMiddleware::class);
        $group->GET('/cardlists', HomeAction::class .':getCardLists')->add(UserAuthMiddleware::class);
        $group->GET('/connectionlists', HomeAction::class .':getConnectionLists')->add(UserAuthMiddleware::class);
        $group->GET('/paidusers', HomeAction::class .':getPaidUsers')->add(UserAuthMiddleware::class);
        $group->GET('/allusers', HomeAction::class .':getAllUsers')->add(UserAuthMiddleware::class);
        $group->GET('/expusers', HomeAction::class .':getExpiredSubscribers')->add(UserAuthMiddleware::class);
        $group->GET('/activesubs', HomeAction::class .':getActiveSubscribers')->add(UserAuthMiddleware::class);
        $group->GET('/freeusers', HomeAction::class .':getFreeUsers')->add(UserAuthMiddleware::class);
        $group->GET('/insub', HomeAction::class .':getSubUsers')->add(UserAuthMiddleware::class);
        $group->GET('/customcards', HomeAction::class .':getCustomCardList')->add(UserAuthMiddleware::class);
        $group->GET('/subuser/delete/{id}', HomeAction::class .':deleteSubUsers')->add(UserAuthMiddleware::class);

        
    });
    
};
