<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Commands;

use Illuminate\Console\Command;

class IndosCheckerLaravelCommand extends Command
{
    public $signature = 'indos-checker-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
