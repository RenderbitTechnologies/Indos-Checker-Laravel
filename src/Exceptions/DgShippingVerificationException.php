<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Exceptions;

use Exception;

class DgShippingVerificationException extends Exception
{
    protected string $indosNumber;

    public function __construct(string $indosNumber, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->indosNumber = $indosNumber;

        if ($message === '') {
            $message = "Failed to verify INDOS number '{$indosNumber}' against DG Shipping portal.";
        }

        parent::__construct($message, $code, $previous);
    }

    public function getIndosNumber(): string
    {
        return $this->indosNumber;
    }
}
