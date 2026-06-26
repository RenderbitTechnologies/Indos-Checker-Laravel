# Contributing to Indos-Checker-Laravel

Thank you for considering a contribution. This document covers everything you need to get set up, write code, and open a pull request.

---

## Table of contents

- [Setup](#setup)
- [Project structure](#project-structure)
- [Running the test suite](#running-the-test-suite)
- [Static analysis](#static-analysis)
- [Code style](#code-style)
- [Commit messages](#commit-messages)
- [Opening a pull request](#opening-a-pull-request)
- [Reporting bugs](#reporting-bugs)
- [Security vulnerabilities](#security-vulnerabilities)
- [License](#license)

---

## Setup

**Requirements:** PHP 8.2+, Composer

```bash
git clone https://github.com/RenderbitTechnologies/Indos-Checker-Laravel.git
cd Indos-Checker-Laravel
composer install
```

Publish the config file (optional, only needed if you want to customise defaults):

```bash
php artisan vendor:publish --tag=indos-checker-laravel-config
```

---

## Project structure

```
src/
  IndosCheckerLaravel.php              # Main package facade / API entry point
  IndosCheckerLaravelServiceProvider.php # Laravel service provider
  Rules/IndosRule.php                   # Laravel validation rule
  Verifiers/
    DgShippingVerifier.php              # Remote DG Shipping portal verification
    LocalVerifier.php                   # Local format-only verification
  Exceptions/                           # Package-specific exceptions
config/
  indos-checker-laravel.php            # Publishable config file
database/                               # Factories (if applicable)
tests/
  Unit/                                 # Unit tests (no HTTP, no Laravel)
  Feature/                              # Feature tests (with Laravel container)
  Integration/                          # Live portal tests (requires Indian IP)
```

---

## Running the test suite

```bash
composer test
# or directly:
vendor/bin/pest
```

The suite runs in random order (`executionOrder="random"` in `phpunit.xml.dist`) — if a test only fails on certain orderings, that is a bug in the test.

### Live portal integration tests

Tests in `tests/Integration/` make real HTTP requests to the DG Shipping portal and are skipped by default. The portal is geo-restricted to Indian IP ranges.

```bash
INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online
```

Run these from an Indian network, a Mumbai-region cloud instance, or an Indian VPN.

### Code coverage

```bash
composer test-coverage
```

Coverage reports are written to `build/coverage/` (HTML) and `build/coverage.txt` (text summary).

---

## Static analysis

This project uses [Larastan](https://github.com Larastan/Larastan) (PHPStan level 4) for static analysis.

```bash
composer analyse
```

CI will fail if static analysis reports new errors. If you intentionally suppress an error, add it to `phpstan-baseline.neon` with a comment explaining why.

---

## Code style

This project uses [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) with the config in `.php-cs-fixer.dist.php`.

```bash
# Auto-fix style issues:
composer format

# Check without modifying:
vendor/bin/php-cs-fixer fix --dry-run
```

CI will fail on any style violation. Run the fixer locally before pushing.

---

## Commit messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification:

```
<type>[optional scope]: <description>

[optional body]
```

| Type | When to use |
|------|-------------|
| `feat` | New user-facing feature |
| `fix` | Bug fix |
| `test` | Adding or updating tests only |
| `docs` | Documentation only |
| `refactor` | Code change with no feature or bug fix |
| `perf` | Performance improvement |
| `ci` | CI configuration changes |
| `chore` | Maintenance, dependency bumps |

Keep the description under 72 characters, written in the imperative mood (`add`, `fix`, `remove` — not `added`, `fixes`, `removed`).

**Examples:**

```
feat: add batch INDOS verification endpoint

fix: handle timeout when DG Shipping portal is unreachable

test: add edge cases for malformed INDOS numbers

docs: clarify geo-restriction in README
```

---

## Opening a pull request

1. Fork the repository and create a branch from `main`.
2. Make your changes with appropriate tests.
3. Run the full validation suite before pushing:

```bash
composer analyse          # static analysis
composer test             # unit + feature tests
composer format           # auto-fix code style
```

4. Update `CHANGELOG.md` under `[Unreleased]` with a summary of your change.
5. Open a pull request against `main`. Use the PR template — it will guide you through the required sections.

**PR requirements:**

- All CI checks must pass (tests, PHPStan, PHP CS Fixer).
- New behaviour must include tests. PRs that add features without tests will not be merged.
- Bug fixes should include a regression test that fails without the fix.
- Breaking changes must be clearly labelled and documented with a migration path.

---

## Reporting bugs

Open an issue using the [Bug Report](https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/issues/new?template=bug_report.yml) template. Include:

- Package version, PHP version, and Laravel version
- A minimal reproduction (input value, config, expected vs actual result)
- The full exception message and stack trace if applicable

For issues with remote verification results specifically, use the [Verification Issue](https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/issues/new?template=verification_issue.yml) template.

---

## Security vulnerabilities

**Do not open a public issue for security vulnerabilities.** Please review our [security policy](https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/blob/main/SECURITY.md) for responsible disclosure instructions.

---

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).
