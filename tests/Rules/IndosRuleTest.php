<?php

use Illuminate\Support\Facades\Validator;
use RenderbitTechnologies\IndosCheckerLaravel\Rules\IndosRule;

it('passes validation for valid INDOS number', function () {
    $validator = Validator::make(
        ['indos' => 'IND1234567'],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeFalse();
});

it('fails validation for invalid INDOS number', function () {
    $validator = Validator::make(
        ['indos' => 'INVALID'],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('indos'))->toContain('INDOS');
});

it('fails validation for empty string', function () {
    $validator = Validator::make(
        ['indos' => ''],
        ['indos' => ['required', new IndosRule()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('fails validation for non-string value', function () {
    $validator = Validator::make(
        ['indos' => 1234567],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('fails validation for INDOS number with wrong length', function () {
    $validator = Validator::make(
        ['indos' => 'IND12345'],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('passes validation for lowercase IND prefix', function () {
    $validator = Validator::make(
        ['indos' => 'ind1234567'],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeFalse();
});
