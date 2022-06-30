<?php

namespace RenderbitTechnologies\IndosCheckerLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use RenderbitTechnologies\IndosCheckerLaravel\Commands\IndosCheckerLaravelCommand;

class IndosCheckerLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('indos-checker-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_indos-checker-laravel_table')
            ->hasCommand(IndosCheckerLaravelCommand::class);
    }
}
