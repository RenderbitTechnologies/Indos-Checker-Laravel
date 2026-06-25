# Changelog

All notable changes to `Indos-Checker-Laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).


---

## v1.0.0 - 2026-06-25

### What's Changed

* Bump dependabot/fetch-metadata from 1.3.1 to 1.3.3 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/1
* Bump dependabot/fetch-metadata from 1.3.3 to 1.3.4 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/2
* Bump dependabot/fetch-metadata from 1.3.4 to 1.3.5 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/3
* Bump ramsey/composer-install from 1 to 2 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/4
* Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/5
* Bump dependabot/fetch-metadata from 1.3.6 to 1.4.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/6
* Bump dependabot/fetch-metadata from 1.4.0 to 1.5.1 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/7
* Bump dependabot/fetch-metadata from 1.5.1 to 1.6.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/8
* Bump actions/checkout from 3 to 4 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/9
* Bump stefanzweifel/git-auto-commit-action from 4 to 5 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/10
* Bump ramsey/composer-install from 2 to 3 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/11
* Bump dependabot/fetch-metadata from 1.6.0 to 2.0.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/12
* Bump dependabot/fetch-metadata from 2.0.0 to 2.1.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/13
* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/14
* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/15
* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/16
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/17
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/18
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/19
* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/20
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/21
* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/22
* Bump dependabot/fetch-metadata from 2.5.0 to 3.0.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/23
* Bump dependabot/fetch-metadata from 3.0.0 to 3.1.0 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/24
* Bump actions/checkout from 6 to 7 by @dependabot[bot] in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/25
* Implement INDOS number validation and DG Shipping verification by @soham2008xyz in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/26

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/1
* @soham2008xyz made their first contribution in https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/pull/26

**Full Changelog**: https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/commits/v1.0.0

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
