<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Exceptions;

use Exception;

class InvalidIndosException extends Exception
{
    protected string $indosNumber;
    protected array $errors;

    public function __construct(string $indosNumber, array $errors = [])
    {
        $this->indosNumber = $indosNumber;
        $this->errors = $errors;

        $message = "Invalid INDOS number: {$indosNumber}";
        if (! empty($errors)) {
            $message .= '. Errors: '.implode(', ', $errors);
        }

        parent::__construct($message);
    }

    public function getIndosNumber(): string
    {
        return $this->indosNumber;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
