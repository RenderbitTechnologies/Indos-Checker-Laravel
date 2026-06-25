<?php

use RenderbitTechnologies\IndosCheckerLaravel\Facades\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel as IndosChecker;

it('validates a correct INDOS number', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('18NM1234'))->toBeTrue();
    expect($checker->isValid('00AA0000'))->toBeTrue();
    expect($checker->isValid('99ZZ9999'))->toBeTrue();
});

it('rejects INDOS number with wrong length', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('18NM123'))->toBeFalse();
    expect($checker->isValid('18NM12345'))->toBeFalse();
    expect($checker->isValid('18NM1'))->toBeFalse();
});

it('rejects INDOS number with wrong format', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('ABCD1234'))->toBeFalse();
    expect($checker->isValid('12345678'))->toBeFalse();
    expect($checker->isValid('18121234'))->toBeFalse();
});

it('rejects INDOS number with non-digit characters in serial', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('18NM12AB'))->toBeFalse();
    expect($checker->isValid('18NM1-34'))->toBeFalse();
    expect($checker->isValid('18NM1 34'))->toBeFalse();
    expect($checker->isValid('18NM12!4'))->toBeFalse();
});

it('accepts lowercase port code and normalizes', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('18nm1234'))->toBeTrue();
    expect($checker->isValid('18Nm1234'))->toBeTrue();
});

it('rejects empty and blank strings', function () {
    $checker = new IndosChecker();

    expect($checker->isValid(''))->toBeFalse();
    expect($checker->isValid('   '))->toBeFalse();
});

it('returns validation errors', function () {
    $checker = new IndosChecker();

    $errors = $checker->validate('BAD');
    expect($errors)->toBeArray();
    expect($errors)->not->toBeEmpty();
    expect($errors)->toContain('The INDoS number format is invalid. Expected format: YYCCSSSS (e.g., 18NM1234).');
});

it('returns empty errors for valid INDOS number', function () {
    $checker = new IndosChecker();

    $errors = $checker->validate('18NM1234');
    expect($errors)->toBeArray();
    expect($errors)->toBeEmpty();
});

it('normalizes INDOS number format', function () {
    $checker = new IndosChecker();

    expect($checker->format('18nm1234'))->toBe('18NM1234');
    expect($checker->format('  18NM1234  '))->toBe('18NM1234');
    expect($checker->format('18Nm1234'))->toBe('18NM1234');
});

it('works through the facade', function () {
    expect(IndosCheckerLaravel::isValid('18NM1234'))->toBeTrue();
    expect(IndosCheckerLaravel::isValid('INVALID'))->toBeFalse();
});

it('validates INDOS numbers with various port code combinations', function () {
    $checker = new IndosChecker();

    $validNumbers = [
        '10NM1000',
        '11GL2000',
        '12MH3000',
        '13KL4000',
        '14TN5000',
        '15AP6000',
        '16WB7000',
        '17GJ8000',
        '18OR9000',
    ];

    foreach ($validNumbers as $number) {
        expect($checker->isValid($number))->toBeTrue()->and($number)->toBeString();
    }
});

it('rejects INDOS numbers with special characters', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('18NM-234'))->toBeFalse();
    expect($checker->isValid('18NM 234'))->toBeFalse();
    expect($checker->isValid('18NM!234'))->toBeFalse();
    expect($checker->isValid('18NM1234@'))->toBeFalse();
});
