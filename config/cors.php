<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines how your backend handles cross-origin requests.
    | It allows your Vercel frontend and local dev environment to communicate
    | with your Laravel API securely — including cookies and Sanctum sessions.
    |
    */

    // Include any paths your frontend will hit directly.
    // Add "login" and "logout" for Sanctum or session-based auth.
    'paths' => [
        'api/*',
        'login',
        'logout',
        'sanctum/csrf-cookie',
    ],

    // Allow all HTTP methods (GET, POST, PUT, DELETE, etc.)
    'allowed_methods' => ['*'],

    // Explicitly allow your production and local frontend origins
    'allowed_origins' => [
        'https://cloudtask-frontend.vercel.app',
        'http://localhost:5173',
    ],

    // No pattern matching needed here
    'allowed_origins_patterns' => [],

    // Allow all headers (Authorization, X-CSRF-TOKEN, etc.)
    'allowed_headers' => ['*'],

    // Headers to expose to the browser (none required)
    'exposed_headers' => [],

    // Caching time for preflight requests (0 = no cache)
    'max_age' => 0,

    // Allow cookies / credentials across origins
    'supports_credentials' => true,

];
