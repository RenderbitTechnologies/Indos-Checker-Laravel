<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use RenderbitTechnologies\IndosCheckerLaravel\IndosCheckerLaravel;

class IndosRule implements ValidationRule
{
    protected IndosCheckerLaravel $checker;

    public function __construct(?IndosCheckerLaravel $checker = null)
    {
        $this->checker = $checker ?? app(IndosCheckerLaravel::class);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid INDOS number.');

            return;
        }

        $errors = $this->checker->validate($value);

        if (! empty($errors)) {
            $fail($errors[0]);
        }
    }
}
