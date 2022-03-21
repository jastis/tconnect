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

// $settings['db']['host'] = 'teekonect-server.mysql.database.azure.com:3306';
// $settings['db']['name'] = 'trausox1_tkonect_db';
// $settings['db']['username'] = 'trausox';
// $settings['db']['password'] = 'Thedirector@1';

// $settings['smtp']['username'] = 'donotreply@trausox.com';
// $settings['smtp']['password'] = '[gEW]tR!UcTa';

// $settings['security']['secretKey'] = 'bDS6lzFqvvSQ8ALbOxatm7(Vk7mLQyzqaS34Q4oR1ew=';
// $settings['security']['serverName'] = 'www.trausox.com';

// $settings['sms']['asid'] = 'AC2c5791d5bf5e1ff3e207fc1fce9fcb7c';
// $settings['sms']['token'] = 'QUMyYzU3OTFkNWJmNWUxZmYzZTIwN2ZjMWZjZTlmY2I3YzoyNjYxYTE4YWEwZWI2MWRlMzA0YjA1YzA1YjdmZTAzZA==';
// $settings['sms']['msid'] = 'MG38881ba865c86ec9fa569920a5864997';

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