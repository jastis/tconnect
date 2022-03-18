<?php
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Odan\Session\Middleware\SessionMiddleware;

return function (App $app) {
// Parse json, form data and xml
$app->addBodyParsingMiddleware();
// Add the Slim built-in routing middleware
$app->addRoutingMiddleware();
// Catch exceptions and errors
$app->add(ErrorMiddleware::class);
//use session
$app->add(SessionMiddleware::class);
};