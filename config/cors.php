<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Required for Sanctum stateful (cookie-based) SPA authentication.
    |
    |  SAMPURNA PAKE REKS: Mengizinkan localhost dan domain Vercel frontend lu
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'],

    // Daftarkan localhost dan domain vercel frontend lu secara eksplisit di sini blay
    'allowed_origins' => [
        'http://localhost:3000',
        'https://prime-property-frontend-three.vercel.app'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Set false karena di apiClient frontend lu tadi: withCredentials: false
    'supports_credentials' => false,

];