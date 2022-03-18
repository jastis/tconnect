<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;


return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $settings = require __DIR__ . '../../env.php';
    
    $containerBuilder->addDefinitions([
        'settings' => ['displayErrorDetails' => true, // Should be set to false in production
        'app_env' => 'DEVELOPMENT',
        'root' => dirname(__DIR__),
        'temp' => dirname(__DIR__) . '/tmp',
        'public' => dirname(__DIR__) . '/public',
        'twig' => [
            'path' => dirname(__DIR__) . '/templates',
            // Should be set to true in production
            'cache_enabled' => false,
            'cache_path' => dirname(__DIR__) . '/tmp/twig-cache'
        ],
        'security' => [
            'secretKey'  => $settings['security']['secretKey'],
            'serverName' => $settings['security']['serverName'],
        ],
        'assets' => [
            // Public assets cache directory
            'path' => dirname(__DIR__) . '/public/cache',
            'productuploadpath' => dirname(__DIR__) . '/public/upload/product',
            'categoryuploadpath' => dirname(__DIR__) . '/public/upload/category',
            'branduploadpath' => dirname(__DIR__) . '/public/upload/brand',
            'fileuploadpath' => dirname(__DIR__) . '/public/upload/files',
            'proofuploadpath' => dirname(__DIR__) . '/public/upload/receipts',
            'url_base_path' => '/cache/',
            // Cache settings
            'cache_enabled' => true,
            'cache_path' => dirname(__DIR__) . '/tmp',
            'cache_name' => 'assets-cache',
            //  Should be set to 1 (enabled) in production
            'minify' => 0,
        ],
        'session' => [
            'name' => 'Ahia_app',
            'cache_expire' => 0,
            'cookie_httponly' => true,
            //'cookie_secure' => true,
        ],
    
        'logger' => [
            'name' => 'AHIA-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => Logger::DEBUG,
        ],
    
        'smtp' => [
            // use 'null' for the null adapter
            'type' => 'smtp',
            'host' => 'smtp.mailtrap.io',
            'port' => '2525',
            'username' => $settings['smtp']['username'],
            'password' => $settings['smtp']['password'], 
        ],
        'sms' =>[
            'username' => $settings['sms']['username'],
            'password' => $settings['sms']['password'], 
        ],
        'db' =>  [
            'driver' => \Cake\Database\Driver\Mysql::class,
            'host' => $settings['db']['host'],
            'database' => $settings['db']['name'],
            'username' => $settings['db']['username'] ,
            'password' => $settings['db']['password'] ,
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            // Enable identifier quoting
            'quoteIdentifiers' => true,
            // Set to null to use MySQL servers timezone
            'timezone' => null,
            // PDO options
            'flags' => [
            // Turn off persistent connections
            PDO::ATTR_PERSISTENT => false,
            // Enable exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Emulate prepared statements
            PDO::ATTR_EMULATE_PREPARES => true,
            // Set default fetch mode to array
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Set character set
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
            ],
        ],
        
     ],

    ]);
};
