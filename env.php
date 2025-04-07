<?php



$settings['db']['host'] = 'teekonect-db.mysql.database.azure.com:3306';  // --------------- azure
$settings['db']['name'] = 'teekonect-app-database';
$settings['db']['username'] = 'yigrwmepxr';
$settings['db']['password'] = 'UMPX640UID2A8073$';

// $settings['db']['host'] = 'localhost:8889';  // --------------- local host 
// $settings['db']['name'] = 'briisico_teekonect_db';
// $settings['db']['username'] = 'root';
// $settings['db']['password'] = 'root';


// $settings['db']['host'] = 'localhost:3306';   // ------------ shared Hosting
// $settings['db']['name'] = 'briisico_teekonect_db';
// $settings['db']['username'] = 'briisico_admin';
// $settings['db']['password'] = '[gEW]tR!UcTa';

$settings['smtp']['username'] = 'donotreply@briisi.com';
$settings['smtp']['password'] = 'Pa55w0rd123456789';

$settings['security']['secretKey'] = 'bDS6lzFqvvSQ8ALbOxatm7(Vk7mLQyzqaS34Q4oR1ew=';
$settings['security']['serverName'] = 'www.briisi.com';

$settings['sms']['asid'] = 'AC2c5791d5bf5e1ff3e207fc1fce9fcb7c';
$settings['sms']['token'] = 'QUMyYzU3OTFkNWJmNWUxZmYzZTIwN2ZjMWZjZTlmY2I3YzoyNjYxYTE4YWEwZWI2MWRlMzA0YjA1YzA1YjdmZTAzZA==';
$settings['sms']['msid'] = 'MG38881ba865c86ec9fa569920a5864997';



$settings['cost']['foreign'] = [
    'customCard' => 2,
    'template' => 12,
    'currency' => 'USD',
    'symbol' => '$',
    'design' => 10,
    'quarterly' => 0.75,
    'biannual' => 0.7,
    'yearly' => 0.65
];
$settings['cost']['local'] = [
    'customCard' => 10000,
    'template' => 9000,
    'currency' => 'NGN',
    'symbol' => '₦',
    'design' => 10000,
    'quarterly' => 0.5,
    'biannual' => 0.5,
    'yearly' => 0.5
];
return $settings;