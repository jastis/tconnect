<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\Middleware\SessionMiddleware;
use App\Database\Transaction;
use App\Database\TransactionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Mime\BodyRendererInterface;
use Cake\Database\Connection;
use Slim\App;
use Slim\Factory\AppFactory;


//use Slim\Views\Twig;


return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
         \Twig\Environment::class => function (ContainerInterface $c): Environment {
            $settings = (array)$c->get('settings');
            $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
            $twig = new Twig\Environment($loader, [
                __DIR__ . '/../var/cache'
            ]);
            if ($settings['app_env'] === 'DEVELOPMENT') {
                $twig->enableDebug();
            }
            $publicPath = (string)$settings['public'];
            // Add extensions
            $twig->addExtension(new \Fullpipe\TwigWebpackExtension\WebpackExtension(
                // The manifest file.
                $publicPath . '/assets/manifest.json',
                // The public path
                $publicPath
            ));
    
            return $twig;
        },
        SessionInterface::class => function (ContainerInterface $container) {
            $settings = $container->get('settings');
            $session = new PhpSession();
            $session->setOptions((array)$settings['session']);
    
            return $session;
        },
    
        SessionMiddleware::class => function (ContainerInterface $container) {
            return new SessionMiddleware($container->get(SessionInterface::class));
        },

        Connection::class => function (ContainerInterface $container) {
            return new Connection($container->get('settings')['db']);
            },

            PDO::class => function (ContainerInterface $container) {
            $db = $container->get(Connection::class);
            $driver = $db->getDriver();
            $driver->connect();
            return $driver->getConnection();
            },
        TransactionInterface::class => function (ContainerInterface $container) {
            return new Transaction($container->get(Connection::class));
            },

            MailerInterface::class => function (ContainerInterface $container) {
                $settings = $container->get('settings')['smtp'];
                // or
             // $settings = $container->get('settings')['smtp'];
                
                // smtp://user:pass@smtp.example.com:25
                $dsn = sprintf(
                    '%s://%s:%s@%s:%s',
                    $settings['type'],
                    $settings['username'],
                    $settings['password'],
                    $settings['host'],
                    $settings['port']
                );
        
                return new Mailer(Transport::fromDsn($dsn));
            },      
        
            BodyRendererInterface::class => function(ContainerInterface $container)
            {
                return new BodyRenderer($container->get(\Twig\Environment::class));
            },
            App::class => function (ContainerInterface $container) {
                AppFactory::setContainer($container);
        
                return AppFactory::create();
            },
            ResponseFactoryInterface::class => function (ContainerInterface $container) {
                return $container->get(App::class)->getResponseFactory();
            },
    ]);
};
