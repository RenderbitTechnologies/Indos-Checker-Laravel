<?php

namespace RenderbitTechnologies\IndosCheckerLaravel;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;
use RenderbitTechnologies\IndosCheckerLaravel\Services\DgShippingVerifier;

class IndosCheckerLaravel
{
    protected string $format;
    protected ?string $dgShippingUrl;
    protected int $timeout;
    protected bool $cacheVerification;
    protected int $cacheTtl;
    protected ?DgShippingVerifier $verifier = null;

    public function __construct()
    {
        $this->format = config('indos-checker-laravel.format', '/^IND\d{7}$/');
        $this->dgShippingUrl = config('indos-checker-laravel.dg_shipping_url');
        $this->timeout = config('indos-checker-laravel.timeout', 30);
        $this->cacheVerification = config('indos-checker-laravel.cache_verification', true);
        $this->cacheTtl = config('indos-checker-laravel.cache_ttl', 1440);
    }

    /**
     * Validate an INDOS number against the format pattern.
     *
     * @return array<int, string> List of validation error messages (empty if valid)
     */
    public function validate(string $indosNumber): array
    {
        $errors = [];

        if ($indosNumber === '') {
            $errors[] = 'The INDOS number is required.';

            return $errors;
        }

        if (! is_string($indosNumber)) {
            $errors[] = 'The INDOS number must be a string.';

            return $errors;
        }

        $indosNumber = trim($indosNumber);

        if ($indosNumber === '') {
            $errors[] = 'The INDOS number cannot be blank.';

            return $errors;
        }

        if (strlen($indosNumber) !== 10) {
            $errors[] = 'The INDOS number must be exactly 10 characters (IND + 7 digits).';
        }

        if (! preg_match('/^IND/i', $indosNumber)) {
            $errors[] = 'The INDOS number must start with "IND".';
        }

        if (strtoupper(substr($indosNumber, 0, 3)) === 'IND') {
            $digits = substr($indosNumber, 3);
            if (! ctype_digit($digits)) {
                $errors[] = 'The INDOS number must have 7 digits after "IND".';
            }
        }

        if (empty($errors) && ! preg_match($this->format, $indosNumber)) {
            $errors[] = 'The INDOS number format is invalid.';
        }

        return $errors;
    }

    /**
     * Check if an INDOS number is valid.
     */
    public function isValid(string $indosNumber): bool
    {
        return $this->validate($indosNumber) === [];
    }

    /**
     * Normalize an INDOS number to uppercase.
     */
    public function format(string $indosNumber): string
    {
        return strtoupper(trim($indosNumber));
    }

    /**
     * Verify an INDOS number against the DG Shipping portal.
     *
     * @return array{valid: bool, indos_number: string, verified_at: string, raw_response?: mixed}
     *
     * @throws DgShippingVerificationException
     * @throws InvalidIndosException
     */
    public function verify(string $indosNumber): array
    {
        $indosNumber = $this->format($indosNumber);

        $errors = $this->validate($indosNumber);
        if (! empty($errors)) {
            throw new InvalidIndosException($indosNumber, $errors);
        }

        if ($this->dgShippingUrl === null) {
            throw new DgShippingVerificationException(
                $indosNumber,
                'DG Shipping verification is not configured. Set dg_shipping_url in config.'
            );
        }

        $cacheKey = "indos_verification_{$indosNumber}";

        if ($this->cacheVerification) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $verifier = $this->getVerifier();
        $result = $verifier->verify($indosNumber);

        if ($this->cacheVerification) {
            Cache::put($cacheKey, $result, $this->cacheTtl * 60);
        }

        return $result;
    }

    /**
     * Get the DG Shipping verifier instance.
     */
    public function getVerifier(): DgShippingVerifier
    {
        if ($this->verifier === null) {
            $this->verifier = new DgShippingVerifier(
                $this->dgShippingUrl,
                $this->timeout
            );
        }

        return $this->verifier;
    }
}
