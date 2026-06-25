<?php

namespace RenderbitTechnologies\IndosCheckerLaravel\Services;

use Illuminate\Support\Facades\Http;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;

class DgShippingVerifier
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(string $baseUrl, int $timeout = 30)
    {
        $this->baseUrl = $baseUrl;
        $this->timeout = $timeout;
    }

    /**
     * Verify an INDOS number against the DG Shipping portal.
     *
     * @return array{valid: bool, indos_number: string, verified_at: string, raw_response?: mixed}
     *
     * @throws DgShippingVerificationException
     */
    public function verify(string $indosNumber): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'User-Agent' => 'IndosCheckerLaravel/1.0',
                ])
                ->post($this->baseUrl, [
                    'IndosNo' => $indosNumber,
                ]);

            if (! $response->successful()) {
                throw new DgShippingVerificationException(
                    $indosNumber,
                    "DG Shipping portal returned HTTP {$response->status()}."
                );
            }

            $body = $response->body();
            $isValid = $this->parseResponse($body, $indosNumber);

            return [
                'valid' => $isValid,
                'indos_number' => $indosNumber,
                'verified_at' => now()->toIso8601String(),
                'raw_response' => $body,
            ];
        } catch (\Exception $e) {
            if ($e instanceof DgShippingVerificationException) {
                throw $e;
            }

            throw new DgShippingVerificationException(
                $indosNumber,
                "Network error while verifying INDOS number: {$e->getMessage()}",
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Parse the DG Shipping portal response to determine validity.
     *
     * Uses a fail-closed approach: only explicit success patterns return true.
     * Unknown or ambiguous response formats are treated as invalid.
     */
    protected function parseResponse(string $body, string $indosNumber): bool
    {
        $bodyLower = strtolower($body);

        // Explicit error indicators — fail fast
        $errorPatterns = [
            'no record found',
            'invalid indos',
            'indos number not found',
            'no data found',
            'invalid number',
            'not a valid',
        ];

        foreach ($errorPatterns as $pattern) {
            if (str_contains($bodyLower, $pattern)) {
                return false;
            }
        }

        // Explicit success indicators — only trust well-known DG Shipping fields
        $successPatterns = [
            'seafarer name',
            'date of birth',
            'indos no',
            'certificate',
        ];

        foreach ($successPatterns as $pattern) {
            if (str_contains($bodyLower, $pattern)) {
                return true;
            }
        }

        // Fail closed: an unrecognised response cannot be trusted as valid.
        // The INDOS number echoed back in an error page would otherwise be a
        // false positive, so we do not fall back to an INDOS-in-body check.
        return false;
    }
}
