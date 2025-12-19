<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Quick Scan Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Quick Scan card registration feature.
    |
    */

    'quick_scan' => [
        // Timeout in seconds for registration mode
        'timeout_seconds' => env('RFID_QUICK_SCAN_TIMEOUT', 30),
        
        // Cache key prefix for registration sessions
        'cache_prefix' => 'rfid_registration_',
    ],
];
