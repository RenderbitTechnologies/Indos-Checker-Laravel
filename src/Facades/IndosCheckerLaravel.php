<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel as IndosChecker;

/**
 * @method static array validate(string $indosNumber)
 * @method static bool isValid(string $indosNumber)
 * @method static string format(string $indosNumber)
 * @method static array verify(string $indosNumber)
 *
 * @see \RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel
 */
class IndosCheckerLaravel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IndosChecker::class;
    }
}
