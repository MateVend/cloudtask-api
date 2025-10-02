<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your settings for cross-origin requests.
    | Make sure to explicitly allow the frontend origin when using
    | credentials (cookies, sessions, auth tokens).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173', // React frontend (Vite)
        'https://cloudtask-frontend.vercel.app', // Production React (Vercel)
        //'https://your-ngrok-url.ngrok-free.app', // (optional: ngrok dev tunnel)
        //'https://your-production-domain.com',    // (optional: production)
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
