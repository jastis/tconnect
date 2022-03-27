<?php

namespace App\Middleware;

use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use App\Domain\Services\UserService;

final class AuthHeaderMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    
    /**
     * @var SessionInterface
     */
    private $session;
    private $uservices;

    public function __construct(
        ResponseFactoryInterface $responseFactory, 
        SessionInterface $session,
        UserService $uservices
    ) {
        $this->responseFactory = $responseFactory;
        $this->session = $session;
        $this->uservices = $uservices;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface{
        if ($this->uservices->ValidateHeader(implode(" ",$request->getHeader('Authorization')))) {
            // User verification
            return $handler->handle($request);
        }
        $response= $this->responseFactory->createResponse()->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(401);
        
        $response->getBody()->write(json_encode(['error'=> "You need an Authorization Token"]));
        return $response;
    }
}