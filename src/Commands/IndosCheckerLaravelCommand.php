<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Commands;

use Illuminate\Console\Command;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;

class IndosCheckerLaravelCommand extends Command
{
    public $signature = 'indos:check
        {indosNumber : The INDOS number to validate (e.g., 18NM1234)}
        {--verify : Also verify the number against the DGS eSamudra server}
        {--dob= : Date of birth in DD/MM/YYYY format — required when using --verify}';

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

        // eSamudra verification (optional)
        if ($this->option('verify')) {
            $dob = $this->option('dob');

            if (! $dob) {
                $this->error('  --dob is required when using --verify (format: DD/MM/YYYY)');
                $this->newLine();

                return self::FAILURE;
            }

            $this->newLine();
            $this->line('  <info>Verifying against DGS eSamudra server...</info>');

            try {
                $result = $checker->verify($indosNumber, $dob);

                if ($result['valid']) {
                    $this->line('  <info>✓ eSamudra Verification:</info> <fg=green>VALID</fg=green>');
                    if (! empty($result['seafarer'])) {
                        foreach ($result['seafarer'] as $label => $value) {
                            $this->line("    <info>{$label}:</info> {$value}");
                        }
                    }
                } else {
                    $this->line('  <info>✗ eSamudra Verification:</info> <fg=red>INVALID</fg=red>');
                }

                $this->line("    Verified at: <info>{$result['verified_at']}</info>");
            } catch (\Exception $e) {
                $this->error("  ✗ Verification failed: {$e->getMessage()}");
                $this->newLine();

                return self::FAILURE;
            }
        } else {
            $this->newLine();
            $this->line('  <comment>Tip:</comment> Use <info>--verify --dob=DD/MM/YYYY</info> to check against the DGS eSamudra server.');
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
