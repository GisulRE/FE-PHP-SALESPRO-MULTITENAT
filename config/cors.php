<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Cross-Origin Resource Sharing (CORS) Configuration
  |--------------------------------------------------------------------------
  |
  | Here you may configure your settings for cross-origin resource sharing
  | or "CORS". This determines what cross-origin operations may execute
  | in web browsers. You are free to adjust these settings as needed.
  |
  */

  'paths' => [
    '*',

    'sanctum/csrf-cookie',
  ],

  // Allow the common HTTP methods (including POST) explicitly.
  // Using a specific list is safer for some environments than a wildcard.
  'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

  // Origins that are allowed to access the application. Keep '*' for
  // development, or set to an array of specific origins in production.
  'allowed_origins' => ['*'],

  'allowed_origins_patterns' => [],

  // Common request headers used by AJAX/JSON requests and CSRF tokens.
  'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'X-CSRF-TOKEN', 'Accept'],

  'exposed_headers' => [],

  'max_age' => 0,

  'supports_credentials' => false,
];
