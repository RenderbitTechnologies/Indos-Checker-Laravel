<?php

namespace RenderbitTechnologies\IndosCheckerLaravel;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RenderbitTechnologies\IndosCheckerApi\IndosChecker;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\DgShippingVerificationException;
use RenderbitTechnologies\IndosCheckerLaravel\Exceptions\InvalidIndosException;
use RenderbitTechnologies\IndosCheckerLaravel\Services\IndosApiService;

class IndosCheckerLaravel
{
    protected string $format;
    protected ?string $esamudraUrl;
    protected int $timeout;
    protected bool $cacheVerification;
    protected int $cacheTtl;
    protected ?IndosApiService $verifier = null;

    public function __construct()
    {
        $this->format = config('indos-checker-laravel.format', '/^\d{2}[A-Z]{2}\d{4}$/i');
        $this->esamudraUrl = config('indos-checker-laravel.esamudra_url');
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
            $errors[] = trans('indos-checker-laravel::validation.required');

            return $errors;
        }

        $indosNumber = trim($indosNumber);

        if ($indosNumber === '') {
            $errors[] = trans('indos-checker-laravel::validation.blank');

            return $errors;
        }

        if (! preg_match($this->format, $indosNumber)) {
            $errors[] = trans('indos-checker-laravel::validation.invalid_format');
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
     * Verify an INDOS number and date of birth against the DGS eSamudra server.
     *
     * @param  string  $dob  Date of birth in DD/MM/YYYY format
     * @return array{valid: bool, indos_number: string, verified_at: string, seafarer: array}
     *
     * @throws InvalidIndosException
     * @throws DgShippingVerificationException
     */
    public function verify(string $indosNumber, string $dob): array
    {
        $indosNumber = $this->format($indosNumber);

        $errors = $this->validate($indosNumber);
        if (! empty($errors)) {
            throw new InvalidIndosException($indosNumber, $errors);
        }

        if ($this->esamudraUrl === null) {
            throw new DgShippingVerificationException(
                $indosNumber,
                'eSamudra verification is not configured. Set esamudra_url in config.'
            );
        }

        $cacheKey = "indos_verification_{$indosNumber}";

        if ($this->cacheVerification) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $result = $this->getVerifier()->verify($indosNumber, $dob);

        if ($this->cacheVerification && $result['valid']) {
            Cache::put($cacheKey, $result, $this->cacheTtl * 60);
        }

        return $result;
    }

    /**
     * Get the eSamudra verifier instance.
     */
    public function getVerifier(): IndosApiService
    {
        if ($this->verifier === null) {
            $client = new Client(['timeout' => $this->timeout]);
            $checker = new IndosChecker($client, $this->esamudraUrl);
            $this->verifier = new IndosApiService($checker);
        }

        return $this->verifier;
    }
}
