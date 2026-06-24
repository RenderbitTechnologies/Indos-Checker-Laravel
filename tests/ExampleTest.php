<?php

use RenderbitTechnologies\IndosCheckerLaravel\Facades\IndosCheckerLaravel;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel as IndosChecker;

it('validates a correct INDOS number', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('IND1234567'))->toBeTrue();
    expect($checker->isValid('IND0000000'))->toBeTrue();
    expect($checker->isValid('IND9999999'))->toBeTrue();
});

it('rejects INDOS number with wrong length', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('IND123456'))->toBeFalse();
    expect($checker->isValid('IND12345678'))->toBeFalse();
    expect($checker->isValid('IND1234'))->toBeFalse();
});

it('rejects INDOS number without IND prefix', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('ABC1234567'))->toBeFalse();
    expect($checker->isValid('1234567890'))->toBeFalse();
    expect($checker->isValid('XD1234567'))->toBeFalse();
});

it('rejects INDOS number with non-digit characters after prefix', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('IND123456A'))->toBeFalse();
    expect($checker->isValid('IND12345AB'))->toBeFalse();
    expect($checker->isValid('INDABCDEFGHI'))->toBeFalse();
});

it('accepts lowercase IND prefix and normalizes', function () {
    $checker = new IndosChecker();

    // The regex is case-insensitive, so ind1234567 should match format
    expect($checker->isValid('ind1234567'))->toBeTrue();
    expect($checker->isValid('Ind1234567'))->toBeTrue();
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
    expect($errors)->toContain('The INDOS number must be exactly 10 characters (IND + 7 digits).');
});

it('returns empty errors for valid INDOS number', function () {
    $checker = new IndosChecker();

    $errors = $checker->validate('IND1234567');
    expect($errors)->toBeArray();
    expect($errors)->toBeEmpty();
});

it('normalizes INDOS number format', function () {
    $checker = new IndosChecker();

    expect($checker->format('ind1234567'))->toBe('IND1234567');
    expect($checker->format('  IND1234567  '))->toBe('IND1234567');
    expect($checker->format('Ind1234567'))->toBe('IND1234567');
});

it('works through the facade', function () {
    expect(IndosCheckerLaravel::isValid('IND1234567'))->toBeTrue();
    expect(IndosCheckerLaravel::isValid('INVALID'))->toBeFalse();
});

it('validates INDOS numbers with various digit combinations', function () {
    $checker = new IndosChecker();

    $validNumbers = [
        'IND1000000',
        'IND2000000',
        'IND3000000',
        'IND4000000',
        'IND5000000',
        'IND6000000',
        'IND7000000',
        'IND8000000',
        'IND9000000',
    ];

    foreach ($validNumbers as $number) {
        expect($checker->isValid($number))->toBeTrue()->and($number)->toBeString();
    }
});

it('rejects INDOS numbers with special characters', function () {
    $checker = new IndosChecker();

    expect($checker->isValid('IND123-4567'))->toBeFalse();
    expect($checker->isValid('IND123 4567'))->toBeFalse();
    expect($checker->isValid('IND1234!67'))->toBeFalse();
    expect($checker->isValid('IND123456@'))->toBeFalse();
});
