<?php

/**
 * Live integration tests against the DG Shipping INDoS portal.
 *
 * These tests make real HTTP requests and are skipped by default.
 * Run them with:
 *
 *   INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online
 *
 * The DG Shipping portal is geo-restricted to Indian IP ranges.
 * Run from an Indian network or via VPN before executing these tests.
 */

use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Services\DgShippingVerifier;

$onlineEnabled = (bool) getenv('INDOS_ONLINE_TEST');

// ---------------------------------------------------------------------------
// Format guard — portal must never be hit with a malformed number
// ---------------------------------------------------------------------------

it('rejects invalid format before any HTTP call is made', function () {
    $checker = new IndosCheckerLaravel();
    $checker->verify('INVALID');
})->throws(InvalidIndosException::class)->group('online');

it('rejects blank string before any HTTP call is made', function () {
    $checker = new IndosCheckerLaravel();
    $checker->verify('');
})->throws(InvalidIndosException::class)->group('online');

// ---------------------------------------------------------------------------
// Live portal tests — skipped unless INDOS_ONLINE_TEST=1
// ---------------------------------------------------------------------------

it('detects a known-invalid INDOS number via the live DG Shipping portal', function () {
    config(['indos-checker-laravel.dg_shipping_url' => 'https://www.dgshipping.gov.in/Content/PageUrl.aspx?page_name=INDOS']);
    config(['indos-checker-laravel.cache_verification' => false]);

    $checker = new IndosCheckerLaravel();

    // 99ZZ0000 uses a year/port/serial that has never been issued
    $result = $checker->verify('99ZZ0000');

    expect($result)->toBeArray()
        ->and($result['indos_number'])->toBe('99ZZ0000')
        ->and($result['valid'])->toBeFalse();
})->skip(! $onlineEnabled, 'Set INDOS_ONLINE_TEST=1 to run live portal tests')->group('online');

it('returns a structured result for a live INDOS lookup', function () {
    config(['indos-checker-laravel.dg_shipping_url' => 'https://www.dgshipping.gov.in/Content/PageUrl.aspx?page_name=INDOS']);
    config(['indos-checker-laravel.cache_verification' => false]);

    $verifier = new DgShippingVerifier(
        'https://www.dgshipping.gov.in/Content/PageUrl.aspx?page_name=INDOS',
        30
    );

    $result = $verifier->verify('18NM1234');

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['valid', 'indos_number', 'verified_at', 'raw_response'])
        ->and($result['indos_number'])->toBe('18NM1234')
        ->and($result['verified_at'])->toBeString();
})->skip(! $onlineEnabled, 'Set INDOS_ONLINE_TEST=1 to run live portal tests')->group('online');

it('throws DgShippingVerificationException on HTTP failure', function () {
    $verifier = new DgShippingVerifier('https://www.dgshipping.gov.in/nonexistent-endpoint', 10);
    $verifier->verify('18NM1234');
})->throws(DgShippingVerificationException::class)
  ->skip(! $onlineEnabled, 'Set INDOS_ONLINE_TEST=1 to run live portal tests')
  ->group('online');
