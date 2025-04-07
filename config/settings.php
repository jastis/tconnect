<?php
$config = require __DIR__ . '../../env.php';
// Error reporting for production
error_reporting(0);
ini_set('display_errors', '0');
// Timezone
date_default_timezone_set('Europe/Berlin');
// Settings
$settings = [];
// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';
// Error Handling Middleware settings
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => false,
    // Parameter is passed to the default ErrorHandler
// View in rendered output by enabling the "displayErrorDetails" setting.
// For the console and unit tests we also disable it
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];
$settings['smtp'] = [
    // use 'null' for the null adapter
    'type' => 'smtp',
    'host' => 'briisi.com',
    'port' => '465',
    'username' => $config['smtp']['username'],
    'password' => $config['smtp']['password'],
];
$settings['assets'] = [
    // Public assets cache directory
    'path' => dirname(__DIR__) . '/public/cache',
    'logopath' => dirname(__DIR__) . '/public/upload/logo',
    'photopath' => dirname(__DIR__) . '/public/upload/photo',
    'customcardpath' => dirname(__DIR__) . '/public/upload/customcard',
    'cardrequestpath' => dirname(__DIR__) . '/public/upload/request',
];

$settings['sms']['asid'] = $config['sms']['aisd'];
$settings['sms']['token'] = $config['sms']['token'];
$settings['sms']['msid'] = $config['sms']['msid'];


$settings['security'] = [
    'secretKey' => $config['security']['secretKey'],
    'serverName' => $config['security']['serverName'],
];

$settings['cost'] = $config['cost'];

$settings['db'] = [
    'driver' => \Cake\Database\Driver\Mysql::class,
    'host' => $config['db']['host'],//'localhost:3308',
    'database' => $config['db']['name'],
    'username' => $config['db']['username'],
    'password' => $config['db']['password'],
    'encoding' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // Enable identifier quoting
    'quoteIdentifiers' => true,
    // Set to null to use MySQL servers timezone
    'timezone' => null,
    // PDO options
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => true,
        //PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Set character set
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ],
    'options' => array(
        PDO::MYSQL_ATTR_SSL_CA => '/home/site/wwwroot/DigiCertGlobalRootCA.crt.pem',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ),
];
return $settings;