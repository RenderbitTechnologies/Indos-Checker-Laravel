<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel
 */
class IndosCheckerLaravel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'indos-checker-laravel';
    }
}
