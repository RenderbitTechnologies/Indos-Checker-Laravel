<?php

use Illuminate\Support\Facades\Validator;
use RenderbitTechnologies\IndosCheckerLaravel\Rules\IndosRule;

it('passes validation for valid INDOS number', function () {
    $validator = Validator::make(
        ['indos' => '18NM1234'],
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
    expect($validator->errors()->first('indos'))->toContain('INDoS');
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
        ['indos' => 18121234],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('fails validation for INDOS number with wrong length', function () {
    $validator = Validator::make(
        ['indos' => '18NM123'],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeTrue();
});

it('passes validation for lowercase port code', function () {
    $validator = Validator::make(
        ['indos' => '18nm1234'],
        ['indos' => [new IndosRule()]]
    );

    expect($validator->fails())->toBeFalse();
});
