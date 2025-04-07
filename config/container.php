<?php
use Cake\Database\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Twig\Environment;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use App\Database\Transaction;
use App\Database\TransactionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Mime\BodyRendererInterface;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\Middleware\SessionMiddleware;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool) $settings['display_error_details'],
            (bool) $settings['log_errors'],
            (bool) $settings['log_error_details']
        );
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
    \Twig\Environment::class => function (ContainerInterface $c): Environment {
        $settings = (array) $c->get('settings');
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
        $twig = new Twig\Environment($loader, [
            __DIR__ . '/../var/cache'
        ]);
        if ($settings['app_env'] === 'DEVELOPMENT') {
            $twig->enableDebug();
        }

        return $twig;
    },
    SessionInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $session = new PhpSession();
        $session->setOptions((array) $settings['session']);

        return $session;
    },

    SessionMiddleware::class => function (ContainerInterface $container) {
        return new SessionMiddleware($container->get(SessionInterface::class));
    },
    TransactionInterface::class => function (ContainerInterface $container) {
        return new Transaction($container->get(Connection::class));
    },

    MailerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['smtp'];

        $dsn = sprintf(
            '%s://%s:%s@%s:%s?%s',
            $settings['type'],
            $settings['username'],
            $settings['password'],
            $settings['host'],
            $settings['port'],
            http_build_query([
                // Solution 1: Bypass SSL verification (temporary/testing only)
                'verify_peer' => 0,

                // OR Solution 2: Use TLS encryption on port 587
                // 'encryption' => 'tls'
            ])
        );

        return new Mailer(Transport::fromDsn($dsn));
    },
    BodyRendererInterface::class => function (ContainerInterface $container) {
        return new BodyRenderer($container->get(\Twig\Environment::class));
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },
];
