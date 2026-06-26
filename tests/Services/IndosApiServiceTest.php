<?php

use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use RenderbitTechnologies\IndosCheckerApi\IndosChecker;
use RenderbitTechnologies\IndosCheckerApi\IndosCheckerException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\Services\IndosApiService;

afterEach(fn () => Mockery::close());

it('returns valid result with seafarer data when INDOS and DOB match', function () {
    /** @var IndosChecker&MockInterface $checker */
    $checker = Mockery::mock(IndosChecker::class);
    $checker->shouldReceive('getData')
        ->once()->with('18NM1234', '01/01/1990')
        ->andReturn([
            'Name' => 'JOHN DOE',
            'Date of Birth' => '01-JAN-1990',
            'INDoS No.' => '18NM1234',
            'Passport No.' => 'M1234567',
            'CDC No.' => 'MUM 001',
            'CDC Issue Place' => 'Mumbai',
        ]);

    $service = new IndosApiService($checker);
    $result = $service->verify('18NM1234', '01/01/1990');

    expect($result)->toBeArray()
        ->and($result['valid'])->toBeTrue()
        ->and($result['indos_number'])->toBe('18NM1234')
        ->and($result['verified_at'])->not->toBeNull()
        ->and($result['seafarer'])->toHaveKey('Name')
        ->and($result['seafarer']['Name'])->toBe('JOHN DOE');
});

it('returns invalid result with empty seafarer data when DOB does not match', function () {
    /** @var IndosChecker&MockInterface $checker */
    $checker = Mockery::mock(IndosChecker::class);
    $checker->shouldReceive('getData')
        ->once()->with('18NM1234', '99/99/9999')
        ->andReturn([]);

    $service = new IndosApiService($checker);
    $result = $service->verify('18NM1234', '99/99/9999');

    expect($result['valid'])->toBeFalse()
        ->and($result['seafarer'])->toBeArray()->toBeEmpty();
});

it('throws DgShippingVerificationException when the API package throws IndosCheckerException', function () {
    /** @var IndosChecker&MockInterface $checker */
    $checker = Mockery::mock(IndosChecker::class);
    $checker->shouldReceive('getData')
        ->andThrow(new IndosCheckerException('Network failure'));

    $service = new IndosApiService($checker);
    $service->verify('18NM1234', '01/01/1990');
})->throws(DgShippingVerificationException::class);

it('result always contains indos_number, valid, verified_at, and seafarer keys', function () {
    /** @var IndosChecker&MockInterface $checker */
    $checker = Mockery::mock(IndosChecker::class);
    $checker->shouldReceive('getData')->andReturn([]);

    $service = new IndosApiService($checker);
    $result = $service->verify('18NM1234', '01/01/1990');

    expect($result)->toHaveKeys(['valid', 'indos_number', 'verified_at', 'seafarer']);
});

// ─── IndosCheckerLaravel integration tests ───────────────────────────────────

function makeChecker(array $seafarerData): IndosCheckerLaravel
{
    $mockIndosChecker = Mockery::mock(IndosChecker::class);
    $mockIndosChecker->shouldReceive('getData')->andReturn($seafarerData);

    $checker = new IndosCheckerLaravel();
    $reflection = new ReflectionProperty($checker, 'verifier');
    $reflection->setAccessible(true);
    $reflection->setValue($checker, new IndosApiService($mockIndosChecker));

    return $checker;
}

it('IndosCheckerLaravel::verify() caches only successful results', function () {
    $checker = makeChecker(['INDoS No.' => '18NM1234', 'Name' => 'TEST USER']);
    $checker->verify('18NM1234', '01/01/1990');

    $cached = Cache::get('indos_verification_18NM1234');
    expect($cached)->toBeArray()
        ->and($cached['valid'])->toBeTrue()
        ->and($cached)->toHaveKey('seafarer');
});

it('IndosCheckerLaravel::verify() does not cache failed results', function () {
    $checker = makeChecker([]);
    $checker->verify('18NM1234', '99/99/9999');

    expect(Cache::get('indos_verification_18NM1234'))->toBeNull();
});

it('IndosCheckerLaravel::verify() returns cached result without hitting eSamudra', function () {
    $cached = [
        'valid' => true,
        'indos_number' => '18NM1234',
        'verified_at' => '2024-01-01T00:00:00+00:00',
        'seafarer' => ['Name' => 'CACHED USER'],
    ];
    Cache::put('indos_verification_18NM1234', $cached, 60);

    $mockIndosChecker = Mockery::mock(IndosChecker::class);
    $mockIndosChecker->shouldReceive('getData')->never();

    $checker = new IndosCheckerLaravel();
    $reflection = new ReflectionProperty($checker, 'verifier');
    $reflection->setAccessible(true);
    $reflection->setValue($checker, new IndosApiService($mockIndosChecker));

    $result = $checker->verify('18NM1234', '01/01/1990');

    expect($result)->toBe($cached);
});

it('IndosCheckerLaravel::verify() throws when esamudra_url is null', function () {
    config(['indos-checker-laravel.esamudra_url' => null]);

    (new IndosCheckerLaravel())->verify('18NM1234', '01/01/1990');
})->throws(DgShippingVerificationException::class, 'not configured');

it('IndosCheckerLaravel::verify() throws InvalidIndosException for bad format', function () {
    (new IndosCheckerLaravel())->verify('INVALID', '01/01/1990');
})->throws(InvalidIndosException::class);

it('IndosCheckerLaravel::verify() normalises INDOS number to uppercase before lookup', function () {
    $checker = makeChecker(['INDoS No.' => '18NM1234', 'Name' => 'JANE DOE']);
    $result = $checker->verify('18nm1234', '01/01/1990');

    expect($result['indos_number'])->toBe('18NM1234');
});
