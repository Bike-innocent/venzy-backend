<?php

// return [
//     'paths' => ['api/*', 'sanctum/csrf-cookie'],
//     'allowed_origins' => ['*'],

//     'allowed_methods' => ['*'],
//     'allowed_origins_patterns' => [],
//     'allowed_headers' => ['*'],
//     'exposed_headers' => [],
//     'max_age' => 0,
//     'supports_credentials' => true,
// ];


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 'allowed_origins' => [
    //     'https://venzy.chibuikeinnocent.tech',
    //     'https://venzy.vercel.app',
    //     'http://locahost/5173',
    // ],
    'allowed_origins' =>  ['*'],

    'allowed_methods' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];