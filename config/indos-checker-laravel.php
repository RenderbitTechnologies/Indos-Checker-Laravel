<?php

// config for RenderbitTechnologies/IndosCheckerLaravel

return [
    /*
    |--------------------------------------------------------------------------
    | INDOS Number Format
    |--------------------------------------------------------------------------
    |
    | The regex pattern used to validate INDOS numbers.
    | DGMA format: 2-digit year + 2-letter port code + 4-digit serial (e.g., 18NM1234).
    |
    */

    'format' => '/^\d{2}[A-Z]{2}\d{4}$/i',

    /*
    |--------------------------------------------------------------------------
    | DG Shipping Verification URL
    |--------------------------------------------------------------------------
    |
    | The URL of the DG Shipping INDoS/COP Checker portal used for
    | online verification of INDOS numbers. Set to null to disable
    | online verification entirely.
    |
    */

    'dg_shipping_url' => 'https://www.dgshipping.gov.in/Content/PageUrl.aspx?page_name=INDOS',

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for HTTP requests to the DG Shipping portal.
    |
    */

    'timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | Cache Verification Results
    |--------------------------------------------------------------------------
    |
    | Whether to cache DG Shipping verification results to avoid
    | repeated HTTP requests. Uses Laravel's Cache facade.
    |
    */

    'cache_verification' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Minutes)
    |--------------------------------------------------------------------------
    |
    | How long (in minutes) to cache DG Shipping verification results.
    | Only applicable when cache_verification is true.
    |
    */

    'cache_ttl' => 60 * 24, // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | The name of the database table to store INDOS verification records.
    |
    */

    'table' => 'indos_checker_laravel_table',
];
