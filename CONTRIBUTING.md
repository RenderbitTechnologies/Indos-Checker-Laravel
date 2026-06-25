# Contributing to Indos-Checker-Laravel

Thank you for considering a contribution. This document covers everything you need to get set up, write code, and open a pull request.

---

## Setup

**Requirements:** PHP 8.2+, Composer

```bash
git clone https://github.com/RenderbitTechnologies/Indos-Checker-Laravel.git
cd Indos-Checker-Laravel
composer install
```

---

## Running the test suite

```bash
composer test
# or directly:
vendor/bin/pest
```

All 34 tests must pass before a PR will be reviewed. The suite runs in random order (`executionOrder="random"` in `phpunit.xml.dist`) — if a test only fails on certain orderings, that is a bug in the test.

### Live portal integration tests

Tests in `tests/Integration/` make real HTTP requests to the DG Shipping portal and are skipped by default. The portal is geo-restricted to Indian IP ranges.

```bash
INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online
```

Run these from an Indian network, a Mumbai-region cloud instance, or an Indian VPN.

---

## Code style

This project uses [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) with the config in `.php-cs-fixer.dist.php`.

```bash
vendor/bin/php-cs-fixer fix
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

---

## Opening a pull request

1. Fork the repository and create a branch from `main`.
2. Make your changes with appropriate tests.
3. Ensure `vendor/bin/pest` and `vendor/bin/php-cs-fixer fix --dry-run` both pass.
4. Update `CHANGELOG.md` under `[Unreleased]` with a summary of your change.
5. Open a pull request against `main`. Include a clear description of the problem and solution.

PRs that add new behaviour without tests, or that break existing tests, will not be merged.

---

## Reporting bugs

Open an issue on [GitHub Issues](https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/issues). Include:

- PHP and Laravel versions
- A minimal reproduction (input value, config, expected vs actual result)
- The full exception message and stack trace if applicable

---

## Security vulnerabilities

**Do not open a public issue for security vulnerabilities.** Please review our [security policy](https://github.com/RenderbitTechnologies/Indos-Checker-Laravel/security/policy) for responsible disclosure instructions.

---

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).
