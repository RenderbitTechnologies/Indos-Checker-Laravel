<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Services\DgShippingVerifier;

it('verifies a valid INDOS number successfully', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Seafarer Name: John Doe, INDOS No: 18NM1234, Date of Birth: 01/01/1990</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('18NM1234');

    expect($result)->toBeArray();
    expect($result['valid'])->toBeTrue();
    expect($result['indos_number'])->toBe('18NM1234');
    expect($result['verified_at'])->not->toBeNull();
});

it('detects invalid INDOS number from DG Shipping response', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>No record found for INDOS number 18NM1234</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('18NM1234');

    expect($result['valid'])->toBeFalse();
});

it('does not treat "invalid" in response body as valid', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>This is an invalid INDoS number.</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('18NM1234');

    expect($result['valid'])->toBeFalse();
});

it('throws exception on HTTP error', function () {
    Http::fake([
        '*' => Http::response('', 500),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);

    $verifier->verify('18NM1234');
})->throws(DgShippingVerificationException::class, 'HTTP 500');

it('throws exception on network error', function () {
    Http::fake([
        '*' => fn () => throw new \Exception('Connection refused'),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);

    $verifier->verify('18NM1234');
})->throws(DgShippingVerificationException::class, 'Network error');

it('works through the main checker with verification', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Seafarer Name: Jane Doe, INDOS No: 19GL0730</body></html>',
            200
        ),
    ]);

    $checker = new IndosCheckerLaravel();
    $result = $checker->verify('19GL0730');

    expect($result['valid'])->toBeTrue();
    expect($result['indos_number'])->toBe('19GL0730');
});

it('rejects invalid format before verifying with DG Shipping', function () {
    $checker = new IndosCheckerLaravel();

    $checker->verify('INVALID');
})->throws(\RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException::class);

it('handles various error patterns in response', function () {
    $errorPatterns = [
        'No record found',
        'Invalid INDOS',
        'INDOS number not found',
        'No data found',
        'Invalid number',
        'Not a valid INDOS',
    ];

    foreach ($errorPatterns as $pattern) {
        Http::fake([
            '*' => Http::response("<html><body>{$pattern}</body></html>", 200),
        ]);

        $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
        $result = $verifier->verify('18NM1234');

        expect($result['valid'])->toBeFalse()->and("Pattern: {$pattern}")->toBeString();
    }
});

// Fix #1 — fail-closed fallback
it('returns invalid for unrecognised response that echoes the INDOS number', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Query result for 18NM1234: unknown status</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('18NM1234');

    // Previously this returned true because the INDOS appeared in the body;
    // with the fail-closed fallback it must return false.
    expect($result['valid'])->toBeFalse();
});

it('returns invalid when response contains neither success nor error patterns', function () {
    Http::fake([
        '*' => Http::response('<html><body>Service temporarily unavailable</body></html>', 200),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('18NM1234');

    expect($result['valid'])->toBeFalse();
});

// Fix #2 — removed the "valid" keyword heuristic; old code would return true here
it('returns invalid when response contains only the word "valid" with no recognised success fields', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Your query is valid.</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('18NM1234');

    // OLD code: "valid" present + "invalid" absent → true (false positive).
    // NEW code: no recognised success pattern → fail closed → false.
    expect($result['valid'])->toBeFalse();
});

// Fix #3 — null dg_shipping_url guard
it('throws exception when dg_shipping_url is not configured', function () {
    config(['indos-checker-laravel.dg_shipping_url' => null]);

    $checker = new IndosCheckerLaravel();
    $checker->verify('18NM1234');
})->throws(DgShippingVerificationException::class, 'not configured');

// Fix #5 — raw_response is stripped before caching
it('does not cache the raw_response field', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Seafarer Name: John Doe, INDOS No: 18NM1234</body></html>',
            200
        ),
    ]);

    $checker = new IndosCheckerLaravel();
    $checker->verify('18NM1234');

    $cached = Cache::get('indos_verification_18NM1234');

    expect($cached)->toBeArray()
        ->and($cached)->toHaveKeys(['valid', 'indos_number', 'verified_at'])
        ->and($cached)->not->toHaveKey('raw_response');
});

// Fix #4 — cache hit path
it('returns cached result without hitting DG Shipping', function () {
    $cached = [
        'valid' => true,
        'indos_number' => '18NM1234',
        'verified_at' => '2024-01-01T00:00:00+00:00',
    ];

    Cache::put('indos_verification_18NM1234', $cached, 60);

    Http::fake();

    $checker = new IndosCheckerLaravel();
    $result = $checker->verify('18NM1234');

    expect($result)->toBe($cached);
    Http::assertNothingSent();
});
