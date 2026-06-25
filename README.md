# INDOS Checker for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/renderbittechnologies/indos-checker-laravel.svg?style=flat-square)](https://packagist.org/packages/renderbittechnologies/indos-checker-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/renderbittechnologies/indos-checker-laravel/run-tests.yml?branch=main&label=tests)](https://github.com/renderbittechnologies/indos-checker-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/renderbittechnologies/indos-checker-laravel.svg?style=flat-square)](https://packagist.org/packages/renderbittechnologies/indos-checker-laravel)

A Laravel package for validating **INDoS (Indian National Database of Seafarers) numbers** as issued by the Directorate General of Shipping (DG Shipping), Mumbai, India.

The package provides:

- **Format validation** — offline regex check against the official `YYCCSSSS` pattern (e.g. `18NM1234`)
- **Remote verification** — optional live lookup against the DG Shipping portal
- **Laravel validation rule** — drop-in `IndosRule` for use in `Validator`/`FormRequest`
- **Result caching** — configurable cache layer to avoid repeated HTTP requests

---

## Requirements

| Laravel | PHP     |
|---------|---------|
| 10.x    | ≥ 8.2   |
| 11.x    | ≥ 8.2   |

---

## Installation

```bash
composer require renderbittechnologies/indos-checker-laravel
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

---

## Configuration

`config/indos-checker-laravel.php`:

```php
return [
    // Regex used for local format validation.
    // DGMA format: 2-digit year + 2-letter port code + 4-digit serial.
    'format' => '/^\d{2}[A-Z]{2}\d{4}$/i',

    // DG Shipping INDoS/COP Checker portal URL.
    // Set to null to disable remote verification entirely.
    'dg_shipping_url' => 'https://www.dgshipping.gov.in/Content/PageUrl.aspx?page_name=INDOS',

    // HTTP timeout in seconds for requests to the DG Shipping portal.
    'timeout' => 30,

    // Cache verification results to avoid repeated HTTP requests.
    'cache_verification' => true,

    // How long (in minutes) to cache results. Default: 24 hours.
    'cache_ttl' => 60 * 24,

    // Database table name for storing verification records.
    'table' => 'indos_checker_laravel_table',
];
```

> **Note:** The DG Shipping portal is hosted on Indian government infrastructure and is only reachable from Indian IP ranges. Remote verification requires an Indian network or a Mumbai-region cloud instance.

---

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

### Remote verification against DG Shipping

`verify()` first validates the format, then performs a live HTTP lookup against the DG Shipping portal. Results are cached for `cache_ttl` minutes.

```php
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;

$checker = new IndosCheckerLaravel();

try {
    $result = $checker->verify('18NM1234');

    // $result shape:
    // [
    //   'valid'        => true,
    //   'indos_number' => '18NM1234',
    //   'verified_at'  => '2024-06-25T10:30:00+00:00',  // ISO 8601
    //   'raw_response' => '<html>...</html>',            // portal HTML (not cached)
    // ]

    if ($result['valid']) {
        // Seafarer record found on DG Shipping portal
    }

} catch (InvalidIndosException $e) {
    // Format is invalid — portal was never contacted
    $e->getIndosNumber();  // the submitted value
    $e->getErrors();       // array of validation error messages

} catch (DgShippingVerificationException $e) {
    // Portal unreachable, returned an HTTP error, or is not configured
    $e->getIndosNumber();  // the INDOS number that was being verified
    $e->getMessage();      // human-readable reason
}
```

#### Response parsing

The verifier uses a **fail-closed** strategy when parsing the portal HTML:

| Response contains | Result |
|-------------------|--------|
| Known error text (`no record found`, `invalid indos`, …) | `valid: false` |
| Known success fields (`seafarer name`, `date of birth`, `indos no`, `certificate`) | `valid: true` |
| Anything else (ambiguous or unrecognised page) | `valid: false` |

The INDOS number appearing anywhere in the response body is **not** treated as a success signal — an error page that echoes back the queried number would otherwise produce a false positive.

#### Caching

Cached entries contain `valid`, `indos_number`, and `verified_at` only. The `raw_response` HTML is intentionally excluded from the cache to avoid persisting large payloads across the 24-hour TTL.

#### Disable remote verification

Set `dg_shipping_url` to `null` in the config (or override at runtime):

```php
config(['indos-checker-laravel.dg_shipping_url' => null]);
```

Calling `verify()` with a null URL throws `DgShippingVerificationException` immediately with the message `"DG Shipping verification is not configured"`.

---

## INDOS Number Format

| Segment | Length | Description                        | Example |
|---------|--------|------------------------------------|---------|
| YY      | 2 digits | Year of registration              | `18`    |
| CC      | 2 letters | Port code (case-insensitive)     | `NM`    |
| SSSS    | 4 digits | Serial number                     | `1234`  |

Full example: **`18NM1234`** — registered in 2018 at New Mangalore port, serial 1234.

---

## Testing

```bash
composer test
```

### Live portal integration tests

The DG Shipping portal is geo-restricted to Indian IP ranges. Integration tests in `tests/Integration/` are skipped by default and must be opted into explicitly:

```bash
INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online
```

Run these from an Indian network, a Mumbai-region CI runner, or over an Indian VPN.

---

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
