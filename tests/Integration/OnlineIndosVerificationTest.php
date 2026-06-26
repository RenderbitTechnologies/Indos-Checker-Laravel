<?php

/**
 * Live integration tests against the DGS eSamudra server.
 *
 * These tests make real HTTP requests and are skipped by default.
 * Run them with:
 *
 *   INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online
 *
 * The DGS eSamudra server (220.156.189.33) is accessible from Indian networks.
 * Run from an Indian network or via VPN before executing these tests.
 */

use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Services\IndosApiService;

$onlineEnabled = (bool) getenv('INDOS_ONLINE_TEST');

// ---------------------------------------------------------------------------
// Format guard — eSamudra must never be hit with a malformed number
// ---------------------------------------------------------------------------

it('rejects invalid format before any HTTP call is made', function () {
    $checker = new IndosCheckerLaravel();
    $checker->verify('INVALID', '01/01/1990');
})->throws(InvalidIndosException::class)->group('online');

it('rejects blank string before any HTTP call is made', function () {
    $checker = new IndosCheckerLaravel();
    $checker->verify('', '01/01/1990');
})->throws(InvalidIndosException::class)->group('online');

// ---------------------------------------------------------------------------
// Live eSamudra tests — skipped unless INDOS_ONLINE_TEST=1
// ---------------------------------------------------------------------------

it('detects a known-invalid INDOS number via the live eSamudra server', function () {
    config(['indos-checker-laravel.cache_verification' => false]);

    $checker = new IndosCheckerLaravel();

    // 99ZZ0000 uses a year/port/serial that has never been issued
    $result = $checker->verify('99ZZ0000', '01/01/1990');

    expect($result)->toBeArray()
        ->and($result['indos_number'])->toBe('99ZZ0000')
        ->and($result['valid'])->toBeFalse();
})->skip(! $onlineEnabled, 'Set INDOS_ONLINE_TEST=1 to run live eSamudra tests')->group('online');

it('returns a structured result for a live INDOS lookup', function () {
    config(['indos-checker-laravel.cache_verification' => false]);

    $checker = new IndosCheckerLaravel();

    // Replace with a real INDOS number and matching DOB for your test environment
    $result = $checker->verify('05LL0262', '14/08/1963');

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['valid', 'indos_number', 'verified_at', 'seafarer'])
        ->and($result['indos_number'])->toBe('05LL0262')
        ->and($result['verified_at'])->toBeString();
})->skip(! $onlineEnabled, 'Set INDOS_ONLINE_TEST=1 to run live eSamudra tests')->group('online');

it('throws DgShippingVerificationException on network failure', function () {
    config(['indos-checker-laravel.esamudra_url' => 'http://0.0.0.0/nonexistent']);

    (new IndosCheckerLaravel())->verify('18NM1234', '01/01/1990');
})->throws(DgShippingVerificationException::class)
  ->skip(! $onlineEnabled, 'Set INDOS_ONLINE_TEST=1 to run live eSamudra tests')
  ->group('online');
