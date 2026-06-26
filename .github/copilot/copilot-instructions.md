# GitHub Copilot Instructions

## Priority Guidelines

When generating code for this repository:

1. **Version Compatibility**: Always detect and respect the exact versions of languages, frameworks, and libraries used in this project
2. **Context Files**: Prioritize patterns and standards defined in the `.github/copilot` directory
3. **Codebase Patterns**: When context files don't provide specific guidance, scan the codebase for established patterns
4. **Architectural Consistency**: Maintain our layered architectural style and established boundaries
5. **Code Quality**: Prioritize maintainability, security, and testability in all generated code

## Technology Version Detection

Before generating code, scan the codebase to identify:

1. **Language Versions**: Detect the exact versions of programming languages in use
   - This project requires **PHP ^8.2** (typed properties, enums, readonly properties, fibers, intersection types, DNF types, and other PHP 8.2 features are available)
   - Never use language features beyond the detected version

2. **Framework Versions**: Identify the exact versions of all frameworks
   - This package supports **Laravel 10.x** and **Laravel 11.x** (`illuminate/*` packages `^10.0|^11.0`)
   - Uses **Orchestra Testbench 8.x** (Laravel 10) and **9.x** (Laravel 11) for package testing
   - Respect version constraints when generating code
   - Never suggest features not available in the detected framework versions

3. **Library Versions**: Note the exact versions of key libraries and dependencies
   - `spatie/laravel-package-tools` ^1.9.2 — for package service provider scaffolding
   - `guzzlehttp/guzzle` ^7.0 — for HTTP requests
   - `renderbit-technologies/indos-checker-api` ^2.1 — the underlying INDOS validation API
   - `pestphp/pest` ^2.0 and `pestphp/pest-plugin-laravel` ^2.0 — testing framework
   - `friendsofphp/php-cs-fixer` ^3.8 — code style
   - `larastan/larastan` ^2.0 — static analysis (PHPStan level 4)
   - Generate code compatible with these specific versions

## Context Files

Prioritize the following files in `.github/copilot` directory (if they exist):

- **architecture.md**: System architecture guidelines
- **tech-stack.md**: Technology versions and framework details
- **coding-standards.md**: Code style and formatting standards
- **folder-structure.md**: Project organization guidelines
- **exemplars.md**: Exemplary code patterns to follow

## Codebase Scanning Instructions

When context files don't provide specific guidance:

1. Identify similar files to the one being modified or created
2. Analyze patterns for:
   - Naming conventions (PascalCase for classes, camelCase for methods/properties, snake_case for config keys and database columns)
   - Code organization (namespace structure, use statement ordering — alphabetical, grouped by vendor)
   - Error handling (custom exception classes extending `Exception`, specific exception types per domain)
   - Logging approaches
   - Documentation style (PHPDoc blocks with `@return`, `@param`, `@throws` annotations)
   - Testing patterns (Pest `it()` closures, `expect()` assertions, Mockery for mocking)
3. Follow the most consistent patterns found in the codebase
4. When conflicting patterns exist, prioritize patterns in newer files or files with higher test coverage
5. Never introduce patterns not found in the existing codebase

## Code Quality Standards

### Maintainability
- Write self-documenting code with clear naming
- Follow the naming and organization conventions evident in the codebase
- Follow established patterns for consistency
- Keep functions focused on single responsibilities
- Limit function complexity and length to match existing patterns
- Use PHP 8.2 typed properties and return type declarations consistently

### Security
- Follow existing patterns for input validation (e.g., `IndosRule` validation rule pattern)
- Apply the same sanitization techniques used in the codebase
- Use parameterized queries matching existing patterns
- Follow established authentication and authorization patterns
- Handle sensitive data according to existing patterns (e.g., environment variables via `env()` for sensitive config)

### Testability
- Follow established patterns for testable code (constructor injection, service classes)
- Match dependency injection approaches used in the codebase (Laravel service container singletons)
- Apply the same patterns for managing dependencies
- Follow established mocking and test double patterns (Mockery mocks, ReflectionProperty for injecting mocks)
- Match the testing style used in existing tests (Pest closures with `it()` / `test()`, `expect()` fluent assertions)

## Documentation Requirements

### Standard
- Follow the exact documentation format found in the codebase
- Match the PHPDoc style and completeness of existing comments
- Document parameters, returns, and exceptions in the same style
- Follow existing patterns for usage examples
- Match class-level documentation style and content
- Use PHPDoc type hints for complex return types (e.g., `@return array{valid: bool, indos_number: string, ...}`)

## Testing Approach

### Unit Testing
- Match the exact structure and style of existing unit tests
- Follow the same naming conventions for test classes and methods (Pest `it()` closures with descriptive strings)
- Use the same assertion patterns found in existing tests (`expect()` fluent API)
- Apply the same mocking approach used in the codebase (Mockery mocks, `shouldReceive()`, `andReturn()`, `andThrow()`)
- Follow existing patterns for test isolation (`afterEach(fn () => Mockery::close())`)

### Integration Testing
- Follow the same integration test patterns found in the codebase
- Match existing patterns for test data setup and teardown (Orchestra Testbench `TestCase` base class)
- Use the same approach for testing component interactions
- Follow existing patterns for verifying system behavior
- Use `->group('online')` for tests that hit external services
- Use `->skip(! $onlineEnabled, '...')` for conditional test execution via environment variables

### Test-Driven Development
- Follow TDD patterns evident in the codebase
- Match the progression of test cases seen in existing code
- Apply the same refactoring patterns after tests pass

## Technology-Specific Guidelines

### PHP Guidelines
- Detect and adhere to the specific PHP version in use (^8.2)
- Use PHP 8.2 features: readonly properties, enums, fibers, intersection types, DNF types, `#[\Override]` attribute
- Follow the exact same design patterns found in the codebase
- Match exception handling patterns from existing code
- Use the same collection types and approaches found in the codebase
- Apply the dependency injection patterns evident in the codebase

### Laravel Guidelines
- Detect and adhere to the specific Laravel version in use (10.x / 11.x)
- Follow the same service provider patterns (use `spatie/laravel-package-tools` `PackageServiceProvider`)
- Match the same singleton registration pattern (`$this->app->singleton()`, `$this->app->alias()`)
- Apply the same configuration patterns (`config()` helper with defaults)
- Follow existing patterns for facades (extending `Illuminate\Support\Facades\Facade`)
- Use Laravel's validation rule contract (`Illuminate\Contracts\Validation\ValidationRule`) for custom rules
- Match the Artisan command patterns (command signature syntax, `$signature`, `$description`, `handle()` return type `int`)
- Follow the same migration patterns (anonymous class migrations, `Blueprint` fluent API)
- Use `trans()` for translatable strings with package namespace (`'indos-checker-laravel::validation.xxx'`)

### Laravel Package Guidelines
- This is a **Laravel package** (not an application), so follow package-specific conventions
- Use `spatie/laravel-package-tools` for service provider registration (`configurePackage()`, `hasConfigFile()`, `hasViews()`, `hasTranslations()`, `hasMigration()`, `hasCommand()`)
- Follow the package namespace convention: `RenderbitTechnologies\IndosCheckerLaravel\`
- Organize source code under `src/` with subdirectories: `Commands/`, `Exceptions/`, `Facades/`, `Models/`, `Rules/`, `Services/`
- Use the `extra.laravel` section in `composer.json` for auto-discovery of providers and aliases

## Code Style and Formatting

This project enforces code style via **PHP CS Fixer** with the following rules:

- **PSR-12** coding standard as the base
- **Short array syntax** (`[]` instead of `array()`)
- **Alphabetically ordered imports** (use statements)
- **No unused imports**
- **Trailing commas** in multiline arrays/parameters
- **Blank lines before** `return`, `throw`, `try`, `break`, `continue`, `declare`
- **Class method separation** with one blank line between methods
- **Spaces after** `not` operator
- **Spaces around** binary operators
- **PHPDoc scalar type hints** and **var without name** formatting

Run `composer format` (or `vendor/bin/php-cs-fixer fix --allow-risky=yes`) to auto-fix formatting.

## Static Analysis

This project uses **Larastan** (PHPStan level 4) for static analysis:

- Run `composer analyse` (or `vendor/bin/phpstan analyse`) before committing
- Respect type hints and return types enforced by PHPStan
- Do not introduce code that would fail PHPStan level 4 analysis
- Refer to `phpstan-baseline.neon` for existing suppressed issues

## Testing Commands

- **Run all tests**: `composer test` (or `vendor/bin/pest`)
- **Run tests with coverage**: `composer test-coverage` (or `vendor/bin/pest --coverage`)
- **Run online integration tests**: `INDOS_ONLINE_TEST=1 vendor/bin/pest --group=online`
- **Run static analysis**: `composer analyse` (or `vendor/bin/phpstan analyse`)
- **Fix code style**: `composer format` (or `vendor/bin/php-cs-fixer fix --allow-risky=yes`)

## Project Architecture

This is a **Laravel package** providing INDOS (Indian National Database of Seafarers) number validation and verification against the Directorate General of Shipping (DGS) eSamudra server.

### Directory Structure

```
src/
├── Commands/                          # Artisan console commands
│   └── IndosCheckerLaravelCommand.php # CLI validation/verification tool
├── Exceptions/                        # Custom exception classes
│   ├── DgShippingVerificationException.php  # API/network errors
│   └── InvalidIndosException.php            # Format validation errors
├── Facades/
│   └── IndosCheckerLaravel.php        # Laravel facade
├── Models/
│   └── IndosRecord.php                # Eloquent model for persistence
├── Rules/
│   └── IndosRule.php                  # Laravel validation rule
├── Services/
│   └── IndosApiService.php            # API service wrapper
├── IndosCheckerLaravel.php            # Main package class (validation, verification, caching)
└── IndosCheckerLaravelServiceProvider.php  # Package service provider
config/
└── indos-checker-laravel.php          # Package configuration
database/
├── factories/
│   └── ModelFactory.php               # Model factory
└── migrations/
    └── create_indos_checker_laravel_table.php.stub  # Migration stub
tests/
├── ExampleTest.php                    # Basic format validation tests
├── Integration/
│   └── OnlineIndosVerificationTest.php # Live eSamudra integration tests
├── Pest.php                           # Pest configuration
├── Rules/
│   └── IndosRuleTest.php              # Validation rule tests
├── Services/
│   └── IndosApiServiceTest.php        # Service unit tests (with mocked API)
└── TestCase.php                       # Base test class (Orchestra Testbench)
```

### Layer Responsibilities

| Layer | Directory | Responsibility |
|-------|-----------|----------------|
| Entry Points | `Commands/`, `Facades/` | CLI commands, Facade access |
| Business Logic | `IndosCheckerLaravel.php` | Validation, verification, caching orchestration |
| Validation | `Rules/` | Laravel validation rule integration |
| Data Access | `Services/`, `Models/` | API communication, optional DB persistence |
| Configuration | `config/` | Package configuration with env support |
| Exceptions | `Exceptions/` | Domain-specific error handling |

### Key Patterns

- **Singleton Registration**: The main `IndosCheckerLaravel` class is registered as a singleton in the service container
- **Facade Pattern**: `IndosCheckerLaravel` facade provides static access to the singleton
- **Service Wrapper**: `IndosApiService` wraps the underlying `IndosChecker` API library
- **Validation Rule**: `IndosRule` implements `ValidationRule` contract for form/request validation
- **Cache-First Verification**: Verification results are cached (only successful results) to avoid repeated HTTP calls
- **Custom Exceptions**: Two exception types — `InvalidIndosException` (format) and `DgShippingVerificationException` (API/network)

## Version Control Guidelines

- Follow Semantic Versioning patterns as applied in the codebase
- Match existing patterns for documenting breaking changes (CHANGELOG.md format)
- Follow the same approach for deprecation notices
- Use conventional commit messages (the CI auto-merges Dependabot PRs)
- The changelog is auto-updated on GitHub releases via `stefanzweifel/changelog-updater-action`

## General Best Practices

- Follow naming conventions exactly as they appear in existing code
- Match code organization patterns from similar files
- Apply error handling consistent with existing patterns
- Follow the same approach to testing as seen in the codebase
- Use the same approach to configuration as seen in the codebase
- **Consistency with existing code takes precedence over external best practices**

## Project-Specific Guidance

- Scan the codebase thoroughly before generating any code
- Respect existing architectural boundaries without exception
- Match the style and patterns of surrounding code
- When in doubt, prioritize consistency with existing code over external best practices
- This package is compatible with both Laravel 10 and 11 — always write code that works in both versions
- Always run `composer analyse`, `composer test`, and `composer format` before committing changes
- Do not modify `phpstan-baseline.neon` unless explicitly addressing a baseline issue
- Do not introduce new dependencies without explicit approval
