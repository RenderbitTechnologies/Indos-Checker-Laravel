# Changelog

All notable changes to `Indos-Checker-Laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

---

## [1.0.0] - 2026-06-25

### Added

- `IndosCheckerLaravel` main class with `validate()`, `isValid()`, `format()`, and `verify()` methods
- `DgShippingVerifier` service — performs live HTTP POST lookups against the DG Shipping INDoS/COP Checker portal
- `IndosRule` — drop-in Laravel `ValidationRule` for use in `Validator` and `FormRequest`
- `IndosCheckerLaravel` facade
- `InvalidIndosException` — thrown when an INDOS number fails local format validation before any HTTP call
- `DgShippingVerificationException` — thrown on HTTP errors, network failures, or missing portal configuration
- Config file (`indos-checker-laravel.php`) with keys: `format`, `dg_shipping_url`, `timeout`, `cache_verification`, `cache_ttl`, `table`
- Result caching via Laravel's Cache facade; TTL configurable in minutes (default: 24 hours)
- `IndosRecord` Eloquent model and database migration for persisting verification records
- `IndosCheckerLaravelCommand` Artisan command stub
- Translations (`resources/lang/en/validation.php`) for all validation messages
- Full test suite (34 tests, 95 assertions) covering format validation, the Laravel rule, remote verification, exception paths, cache hit/miss, and edge cases
- `tests/Integration/OnlineIndosVerificationTest.php` — live portal integration tests, skipped by default; opt in with `INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online`

### Fixed

- **Fail-closed response parsing (#1):** removed the INDOS-number-in-body fallback from `DgShippingVerifier::parseResponse()`; an error page that echoes the queried number back no longer produces a false-positive valid result
- **`valid`/`invalid` keyword heuristic (#2):** removed the ambiguous `str_contains($body, 'valid') && !str_contains($body, 'invalid')` check; any response lacking an explicit success field is now rejected
- **Null URL guard test (#3):** `verify()` throws `DgShippingVerificationException` (message: "not configured") when `dg_shipping_url` is `null`; this code path is now covered by a dedicated test
- **Cache hit path test (#4):** a pre-populated cache entry is returned immediately and `Http::assertNothingSent()` confirms the verifier is never contacted
- **Raw HTML excluded from cache (#5):** `raw_response` (the full portal HTML) is stripped via `array_diff_key` before the result is written to cache, preventing large payloads from being persisted across the 24-hour TTL; the field is still present on the value returned to the caller

### Changed

- Dropped Laravel 9 support; package now targets Laravel 10 and 11 only
- Minimum PHP version raised to 8.2
- CI matrix updated: `orchestra/testbench` pinned to `^9.2` (avoids undeclared-property bug in 9.0.0), `spatie/laravel-package-tools` collision constraint set to `^8.0`
- `phpunit.xml.dist` `failOnWarning` and `failOnRisky` enabled; `executionOrder` set to `random`

### CI / Dependencies

- GitHub Actions: `actions/checkout` bumped from 3 → 4 → 5 → 6 → 7
- GitHub Actions: `dependabot/fetch-metadata` bumped from 1.3.1 → 3.1.0 (via multiple PRs)
- GitHub Actions: `ramsey/composer-install` bumped from 1 → 2 → 3 → 4
- GitHub Actions: `stefanzweifel/git-auto-commit-action` bumped from 4 → 5 → 6 → 7

---

## [0.1.0] - 2022-06-30

### Added

- Initial package scaffold generated from the Spatie Laravel Package Skeleton
- Renderbit Technologies branding and namespace (`RenderbitTechnologies\IndosCheckerLaravel`)
- Basic service provider, facade, and config stubs
