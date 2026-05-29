<?php

return [

    /*
     * Paths that CORS should apply to.
     * 'api/*' covers all API routes.
     */
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:4200',  // Angular dev server
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
