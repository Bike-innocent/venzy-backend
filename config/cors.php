
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => ['*'],

   // 'allowed_origins' => ['http://innoshop.chibuikeinnocent.tech','http://www.innoshop.chibuikeinnocent.tech','https://innoshop.chibuikeinnocent.tech','https://www.innoshop.chibuikeinnocent.tech','http://localhost:5173'],
    'allowed_methods' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
