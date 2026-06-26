<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Services;

use RenderbitTechnologies\IndosCheckerApi\IndosChecker;
use RenderbitTechnologies\IndosCheckerApi\IndosCheckerException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;

class IndosApiService
{
    public function __construct(private IndosChecker $checker)
    {
    }

    /**
     * Verify an INDOS number via the DGS eSamudra server.
     *
     * Calls getData() once; validity is determined by presence of the
     * 'INDoS No.' key in the parsed response (same logic as checkValid()).
     *
     * @return array{valid: bool, indos_number: string, verified_at: string, seafarer: array}
     *
     * @throws DgShippingVerificationException
     */
    public function verify(string $indosNumber, string $dob): array
    {
        try {
            $seafarer = $this->checker->getData($indosNumber, $dob);
            $isValid = isset($seafarer['INDoS No.']);

            return [
                'valid' => $isValid,
                'indos_number' => $indosNumber,
                'verified_at' => now()->toIso8601String(),
                'seafarer' => $isValid ? $seafarer : [],
            ];
        } catch (IndosCheckerException $e) {
            throw new DgShippingVerificationException(
                $indosNumber,
                "eSamudra API error: {$e->getMessage()}",
                (int) $e->getCode(),
                $e
            );
        }
    }
}
