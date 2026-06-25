<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Commands;

use Illuminate\Console\Command;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;

class IndosCheckerLaravelCommand extends Command
{
    public $signature = 'indos:check
        {indosNumber : The INDOS number to validate (e.g., IND1234567)}
        {--verify : Also verify the number against the DG Shipping portal}';

    public $description = 'Validate an INDOS number issued by the Directorate General of Shipping, Mumbai';

    public function handle(): int
    {
        /** @var IndosCheckerLaravel $checker */
        $checker = app(IndosCheckerLaravel::class);

        $indosNumber = strtoupper(trim($this->argument('indosNumber')));

        $this->newLine();
        $this->line("  <info>INDOS Number:</info> {$indosNumber}");
        $this->line('');

        // Format validation
        $errors = $checker->validate($indosNumber);

        if (! empty($errors)) {
            $this->error('  ✗ Format Validation: FAILED');
            foreach ($errors as $error) {
                $this->line("    <error>• {$error}</error>");
            }
            $this->newLine();

            return self::FAILURE;
        }

        $this->line('  <info>✓ Format Validation:</info> <fg=green>PASSED</fg=green>');
        $this->line("    Normalized: <info>{$checker->format($indosNumber)}</info>");

        // DG Shipping verification (optional)
        if ($this->option('verify')) {
            $this->newLine();
            $this->line('  <info>Verifying against DG Shipping portal...</info>');

            try {
                $result = $checker->verify($indosNumber);

                if ($result['valid']) {
                    $this->line('  <info>✓ DG Shipping Verification:</info> <fg=green>VALID</fg=green>');
                } else {
                    $this->line('  <info>✗ DG Shipping Verification:</info> <fg=red>INVALID</fg=red>');
                }

                $this->line("    Verified at: <info>{$result['verified_at']}</info>");
            } catch (\Exception $e) {
                $this->error("  ✗ Verification failed: {$e->getMessage()}");
                $this->newLine();

                return self::FAILURE;
            }
        } else {
            $this->newLine();
            $this->line('  <comment>Tip:</comment> Use <info>--verify</info> to also check against the DG Shipping portal.');
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
