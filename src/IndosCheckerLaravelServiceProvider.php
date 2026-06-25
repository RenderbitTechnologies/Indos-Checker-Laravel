<?php

namespace RenderbitTechnologies\IndosCheckerLaravel;

use RenderbitTechnologies\IndosCheckerLaravel\Commands\IndosCheckerLaravelCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasTranslations()
            ->hasMigration('create_indos_checker_laravel_table')
            ->hasCommand(IndosCheckerLaravelCommand::class);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(IndosCheckerLaravel::class);

        $this->app->alias(IndosCheckerLaravel::class, 'indos-checker-laravel');
    }
}
