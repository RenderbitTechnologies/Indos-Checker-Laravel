<?php

use Mockery\MockInterface;
use RenderbitTechnologies\IndosCheckerApi\IndosChecker;
use RenderbitTechnologies\IndosCheckerApi\IndosCheckerException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Services\IndosApiService;

afterEach(fn () => Mockery::close());

it('returns valid result with seafarer data when INDOS and DOB match', function () {
    /** @var IndosChecker&MockInterface $checker */
    $checker = Mockery::mock(IndosChecker::class);
    $checker->shouldReceive('getData')
        ->once()->with('18NM1234', '01/01/1990')
        ->andReturn([
            'Name'            => 'JOHN DOE',
            'Date of Birth'   => '01-JAN-1990',
            'INDoS No.'       => '18NM1234',
            'Passport No.'    => 'M1234567',
            'CDC No.'         => 'MUM 001',
            'CDC Issue Place' => 'Mumbai',
        ]);

    $service = new IndosApiService($checker);
    $result  = $service->verify('18NM1234', '01/01/1990');

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
    $result  = $service->verify('18NM1234', '99/99/9999');

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
    $result  = $service->verify('18NM1234', '01/01/1990');

    expect($result)->toHaveKeys(['valid', 'indos_number', 'verified_at', 'seafarer']);
});
