# INDOS Checker for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/renderbit-technologies/indos-checker-laravel.svg?style=flat-square)](https://packagist.org/packages/renderbit-technologies/indos-checker-laravel)
[![License](https://img.shields.io/packagist/l/renderbit-technologies/indos-checker-laravel?style=flat-square)](https://packagist.org/packages/renderbit-technologies/indos-checker-laravel)
[![PHP Version Require](https://img.shields.io/packagist/php-v/renderbit-technologies/indos-checker-laravel?style=flat-square)](https://packagist.org/packages/renderbit-technologies/indos-checker-laravel)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/RenderbitTechnologies/indos-checker-laravel/run-tests.yml?branch=main&label=tests)](https://github.com/RenderbitTechnologies/indos-checker-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/renderbit-technologies/indos-checker-laravel.svg?style=flat-square)](https://packagist.org/packages/renderbit-technologies/indos-checker-laravel)

A Laravel package for validating **INDoS (Indian National Database of Seafarers) numbers** as issued by the Directorate General of Shipping (DG Shipping), Mumbai, India.

The package provides:

- **Format validation** — offline regex check against the official `YYCCSSSS` pattern (e.g. `18NM1234`)
- **Remote verification** — optional live lookup against the DGS eSamudra server, returning a full structured seafarer profile
- **Laravel validation rule** — drop-in `IndosRule` for use in `Validator`/`FormRequest`
- **Result caching** — configurable cache layer to avoid repeated HTTP requests

## Requirements

| Laravel | PHP     |
|---------|---------|
| 10.x    | ≥ 8.2   |
| 11.x    | ≥ 8.2   |

## Installation

```bash
composer require renderbit-technologies/indos-checker-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --tag="indos-checker-laravel-config"
```

Optionally publish and run the database migration (stores verification records):

```bash
php artisan vendor:publish --tag="indos-checker-laravel-migrations"
php artisan migrate
```

## Configuration

`config/indos-checker-laravel.php`:

```php
return [
    // Regex used for local format validation.
    // DGMA format: 2-digit year + 2-letter port code + 4-digit serial.
    'format' => '/^\d{2}[A-Z]{2}\d{4}$/i',

    // DGS eSamudra AJAX endpoint used for online verification.
    // Override via the INDOS_ESAMUDRA_URL environment variable,
    // or set to null to disable remote verification entirely.
    'esamudra_url' => env(
        'INDOS_ESAMUDRA_URL',
        'http://220.156.189.33/esamudraUI/checkerajaxservlet'
    ),

    // HTTP timeout in seconds for requests to the eSamudra server.
    'timeout' => 30,

    // Cache successful verification results to avoid repeated HTTP requests.
    // Only valid (true) results are cached; failed lookups are never stored.
    'cache_verification' => true,

    // How long (in minutes) to cache a successful result. Default: 24 hours.
    'cache_ttl' => 60 * 24,

    // Database table name for storing verification records.
    'table' => 'indos_checker_laravel_table',
];
```

> **Note:** The DGS eSamudra server (`220.156.189.33`) runs on HTTP port 80 only — TLS is not yet enabled on the server side. Remote verification works best from Indian networks; test from an Indian network or a Mumbai-region cloud instance.

## Usage

### Format validation (offline)

```php
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;

$checker = new IndosCheckerLaravel();

// Boolean check
$checker->isValid('18NM1234');   // true
$checker->isValid('18nm1234');   // true  — case-insensitive
$checker->isValid('INVALID');    // false

// Returns an array of error messages (empty = valid)
$errors = $checker->validate('18NM123');
// ['The INDoS number format is invalid. Expected format: YYCCSSSS (e.g., 18NM1234).']

// Normalize to uppercase + trimmed
$checker->format('  18nm1234  ');  // '18NM1234'
```

### Via the Facade

```php
use RenderbitTechnologies\IndosCheckerLaravel\Facades\IndosCheckerLaravel;

IndosCheckerLaravel::isValid('18NM1234');   // true
IndosCheckerLaravel::format('18nm1234');    // '18NM1234'
```

### Laravel validation rule

```php
use RenderbitTechnologies\IndosCheckerLaravel\Rules\IndosRule;

// In a FormRequest or manual Validator call:
$request->validate([
    'indos_number' => ['required', new IndosRule()],
]);
```

The rule reports a human-readable error message when validation fails:

```
The INDoS number format is invalid. Expected format: YYCCSSSS (e.g., 18NM1234).
```

### Remote verification via DGS eSamudra

`verify()` first validates the INDOS number format offline, then performs a live lookup against the DGS eSamudra server. A date of birth is required — it is the credential the eSamudra server uses to authenticate the query. Successful results are cached for `cache_ttl` minutes.

```php
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;

$checker = new IndosCheckerLaravel();

try {
    $result = $checker->verify('18NM1234', '14/08/1963');  // DOB in DD/MM/YYYY

    // $result shape when valid:
    // [
    //   'valid'        => true,
    //   'indos_number' => '18NM1234',
    //   'verified_at'  => '2024-06-26T10:30:00+05:30',  // ISO 8601
    //   'seafarer'     => [
    //     'Name'             => 'YADAV SANJEEV',
    //     'Date of Birth'    => '14-AUG-1963',
    //     'INDoS No.'        => '05LL0262',
    //     'Passport No.'     => 'M2069200',
    //     'Passport Issue Date' => '15-SEP-2014',
    //     'Passport Valid To'   => '14-SEP-2024',
    //     'CDC No.'          => 'MUM 133201',
    //     'CDC Issue Date'   => '22-MAY-2015',
    //     'CDC Valid To'     => '21-MAY-2025',
    //     'CDC Issue Place'  => 'Mumbai',
    //   ],
    // ]
    //
    // $result shape when invalid (DOB mismatch or number not found):
    // [
    //   'valid'        => false,
    //   'indos_number' => '18NM1234',
    //   'verified_at'  => '2024-06-26T10:30:00+05:30',
    //   'seafarer'     => [],
    // ]

    if ($result['valid']) {
        $name = $result['seafarer']['Name'];
        $cdc  = $result['seafarer']['CDC No.'];
    }

} catch (InvalidIndosException $e) {
    // Format is invalid — eSamudra was never contacted
    $e->getIndosNumber();  // the submitted value
    $e->getErrors();       // array of validation error messages

} catch (DgShippingVerificationException $e) {
    // eSamudra unreachable, returned an HTTP error, or is not configured
    $e->getIndosNumber();  // the INDOS number that was being verified
    $e->getMessage();      // human-readable reason
}
```

> **DOB format:** Pass the date of birth exactly as `DD/MM/YYYY` (e.g. `14/08/1963`). An `\InvalidArgumentException` is thrown if the format does not match.

#### Caching

Only successful (`valid: true`) results are cached. A failed lookup — wrong DOB, number not found — is never stored, so a subsequent call with the correct DOB will always reach the server. Cached entries contain the full `seafarer` array alongside `valid`, `indos_number`, and `verified_at`.

#### Disable remote verification

Set `esamudra_url` to `null` in the config or via the environment variable:

```php
// config/indos-checker-laravel.php
'esamudra_url' => null,

// or at runtime:
config(['indos-checker-laravel.esamudra_url' => null]);
```

Calling `verify()` when the URL is `null` throws `DgShippingVerificationException` immediately with the message `"eSamudra verification is not configured"`.

### Artisan command

```bash
# Format validation only
php artisan indos:check 18NM1234

# Format validation + live eSamudra verification
php artisan indos:check 18NM1234 --verify --dob=14/08/1963
```

A successful verification prints the full seafarer profile returned by the eSamudra server.

## INDOS Number Format

| Segment | Length | Description                        | Example |
|---------|--------|------------------------------------|---------|
| YY      | 2 digits | Year of registration              | `18`    |
| CC      | 2 letters | Port code (case-insensitive)     | `NM`    |
| SSSS    | 4 digits | Serial number                     | `1234`  |

Full example: **`18NM1234`** — registered in 2018 at New Mangalore port, serial 1234.

## Testing

```bash
composer test
```

### Live eSamudra integration tests

Integration tests in `tests/Integration/` make real HTTP requests to the DGS eSamudra server. They are skipped by default and must be opted into explicitly:

```bash
INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online
```

Run these from an Indian network, a Mumbai-region CI runner, or over an Indian VPN.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Soham Banerjee](https://github.com/RenderbitTechnologies)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
