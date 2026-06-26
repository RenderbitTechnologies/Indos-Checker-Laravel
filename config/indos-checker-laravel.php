<?php

// config for RenderbitTechnologies/IndosCheckerLaravel

return [
    /*
    |--------------------------------------------------------------------------
    | INDOS Number Format
    |--------------------------------------------------------------------------
    |
    | Regex pattern for offline format validation.
    | DGMA format: 2-digit year + 2-letter port code + 4-digit serial (e.g., 18NM1234).
    |
    */

    'format' => '/^\d{2}[A-Z]{2}\d{4}$/i',

    /*
    |--------------------------------------------------------------------------
    | DGS eSamudra URL
    |--------------------------------------------------------------------------
    |
    | Endpoint for the DGS eSamudra AJAX servlet. Set to null to disable
    | online verification. The default value is the public DGS server.
    |
    */

    'esamudra_url' => env(
        'INDOS_ESAMUDRA_URL',
        'http://220.156.189.33/esamudraUI/checkerajaxservlet'
    ),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for HTTP requests to the eSamudra server.
    |
    */

    'timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | Cache Verification Results
    |--------------------------------------------------------------------------
    |
    | Cache successful verification results to avoid repeated HTTP requests.
    | Uses Laravel's Cache facade. Only valid (true) results are cached.
    |
    */

    'cache_verification' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Minutes)
    |--------------------------------------------------------------------------
    |
    | How long to cache a successful verification result. Default: 24 hours.
    |
    */

    'cache_ttl' => 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Database table for optional INDOS record persistence.
    |
    */

    'table' => 'indos_checker_laravel_table',
];
