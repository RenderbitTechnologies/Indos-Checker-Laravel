<?php

use Illuminate\Support\Facades\Http;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Services\DgShippingVerifier;

it('verifies a valid INDOS number successfully', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Seafarer Name: John Doe, INDOS No: IND1234567, Date of Birth: 01/01/1990</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('IND1234567');

    expect($result)->toBeArray();
    expect($result['valid'])->toBeTrue();
    expect($result['indos_number'])->toBe('IND1234567');
    expect($result['verified_at'])->not->toBeNull();
});

it('detects invalid INDOS number from DG Shipping response', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>No record found for INDOS number IND1234567</body></html>',
            200
        ),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);
    $result = $verifier->verify('IND1234567');

    expect($result['valid'])->toBeFalse();
});

it('throws exception on HTTP error', function () {
    Http::fake([
        '*' => Http::response('', 500),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);

    $verifier->verify('IND1234567');
})->throws(DgShippingVerificationException::class, 'HTTP 500');

it('throws exception on network error', function () {
    Http::fake([
        '*' => fn () => throw new \Exception('Connection refused'),
    ]);

    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/test', 30);

    $verifier->verify('IND1234567');
})->throws(DgShippingVerificationException::class, 'Network error');

it('works through the main checker with verification', function () {
    Http::fake([
        '*' => Http::response(
            '<html><body>Seafarer Name: Jane Doe, INDOS No: IND9876543</body></html>',
            200
        ),
    ]);

    $checker = new IndosCheckerLaravel();
    $result = $checker->verify('IND9876543');

    expect($result['valid'])->toBeTrue();
    expect($result['indos_number'])->toBe('IND9876543');
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
        $result = $verifier->verify('IND1234567');

        expect($result['valid'])->toBeFalse()->and("Pattern: {$pattern}")->toBeString();
    }
});
