<?php



$settings['db']['host'] = getenv('AZURE_MYSQL_HOST');
$settings['db']['name'] = getenv('AZURE_MYSQL_DBNAME');
$settings['db']['username'] = getenv('AZURE_MYSQL_USERNAME');
$settings['db']['password'] = getenv('AZURE_MYSQL_PASSWORD');

// $settings['db']['host'] = 'localhost:8889';  // --------------- local host 
// $settings['db']['name'] = 'briisico_teekonect_db';
// $settings['db']['username'] = 'root';
// $settings['db']['password'] = 'root';


// $settings['db']['host'] = 'localhost:3306';   // ------------ shared Hosting
// $settings['db']['name'] = 'briisico_teekonect_db';
// $settings['db']['username'] = 'briisico_admin';
// $settings['db']['password'] = '[gEW]tR!UcTa';

$settings['smtp']['username'] = 'donotreply@briisi.com';
$settings['smtp']['password'] = '[gEW]tR!UcTa';

$settings['security']['secretKey'] = 'bDS6lzFqvvSQ8ALbOxatm7(Vk7mLQyzqaS34Q4oR1ew=';
$settings['security']['serverName'] = 'www.briisi.com';

$settings['sms']['asid'] = 'AC2c5791d5bf5e1ff3e207fc1fce9fcb7c';
$settings['sms']['token'] = 'QUMyYzU3OTFkNWJmNWUxZmYzZTIwN2ZjMWZjZTlmY2I3YzoyNjYxYTE4YWEwZWI2MWRlMzA0YjA1YzA1YjdmZTAzZA==';
$settings['sms']['msid'] = 'MG38881ba865c86ec9fa569920a5864997';



$settings['cost']['foreign']=[
    'customCard'=> 2,
    'template'=>5,
    'currency'=> 'USD',
    'symbol'=> '$',
    'design'=> 1,
    'quarterly' => 1,
    'biannual' =>0.9,
    'yearly' => 0.75
    ];
    $settings['cost']['local']=[
        'customCard'=> 2000,
        'template'=>2500,
        'currency'=> 'NGN',
        'symbol'=> '₦',
        'design'=> 1000,
        'quarterly' => 1,
        'biannual' =>0.9,
        'yearly' => 0.75
        ];

return $settings;