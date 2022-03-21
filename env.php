<?php



$settings['db']['host'] = getenv('DB_HOST');
$settings['db']['name'] = getenv('DB_NAME');
$settings['db']['username'] = getenv('DB_USERNAME');
$settings['db']['password'] = getenv('DB_PASSWORD');

$settings['smtp']['username'] = getenv('SMTP_USERNAME');
$settings['smtp']['password'] = getenv('SMTP_PASSWORD');

$settings['security']['secretKey'] = getenv('SECRET_KEY');
$settings['security']['serverName'] =getenv('SERVER_NAME');

$settings['sms']['asid'] = ('SMS_ASID');
$settings['sms']['token'] =getenv('SMS_TOKEN');
$settings['sms']['msid'] = getenv('SMS_MSID');

$settings['db']['host'] = 'localhost:3306';
$settings['db']['name'] = 'tconnect_db';
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';



$settings['cost']=[
    'customCard'=> 2,
    'template'=>5,
    'currency'=> 'USD',
    'symbol'=> '$',
    'design'=> 1,
    'quarterly' => 1,
    'biannual' =>0.9,
    'yearly' => 0.75
    ];
return $settings;