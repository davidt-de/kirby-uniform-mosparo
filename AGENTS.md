# Kirby Uniform Mosparo - Agent Guide

This guide provides essential information for AI coding agents working on the Kirby Uniform Mosparo plugin.

## Project Overview

**Kirby Uniform Mosparo** is a plugin that integrates [Mosparo](https://mosparo.io) spam protection with [Kirby Uniform](https://github.com/mzur/kirby-uniform) forms. It provides privacy-friendly, GDPR-compliant spam protection as an alternative to reCAPTCHA, hCaptcha, and Cloudflare Turnstile.

- **Package**: `getkirby-uniform/mosparo`
- **Version**: 1.0.0
- **PHP**: >= 8.0
- **License**: MIT
- **Author**: Patrick Davidt <hallo@davidt.de>

## Technology Stack

| Component | Version | Purpose |
|-----------|---------|---------|
| PHP | ^8.0 | Runtime language |
| Kirby CMS | 3.5+ \| 4.x \| 5.x | Content management system |
| Kirby Uniform | ^5.0 | Form handling library |
| Mosparo PHP API Client | ^1.0 | API communication |
| PHPUnit | ^10.0 | Testing framework |
| Mockery | ^1.6 | Mocking library |

## Project Structure

```
.
├── composer.json          # Composer dependencies and scripts
├── composer.lock          # Locked dependency versions
├── index.php              # Main plugin entry point
├── phpunit.xml            # PHPUnit configuration
├── config/
│   └── options.php        # Example configuration file
├── src/                   # Main source code (PSR-4: Uniform\Mosparo\)
│   ├── Config/            # Configuration classes
│   │   ├── Config.php     # Immutable config value object
│   │   └── ConfigFactory.php  # Factory for creating Config
│   ├── Exception/         # Custom exceptions
│   │   └── VerificationException.php
│   ├── Form/              # Form extensions
│   │   └── FormExtensions.php   # Trait for Form class
│   ├── Guards/            # Uniform guards
│   │   └── MosparoGuard.php     # Main spam protection guard
│   ├── Validation/        # Validation services
│   │   └── VerificationService.php
│   ├── Widget/            # Widget rendering
│   │   └── WidgetRenderer.php
│   ├── MosparoPlugin.php  # Main plugin class
│   └── helpers.php        # Global helper functions
├── tests/                 # Test suite (PSR-4: Uniform\Mosparo\Tests\)
│   ├── Config/
│   │   └── ConfigTest.php
│   ├── Guards/
│   │   ├── MosparoGuardTest.php
│   │   └── MosparoGuardSecurityTest.php
│   ├── Validation/
│   │   └── VerificationServiceTest.php
│   ├── Widget/
│   │   └── WidgetRendererTest.php
│   ├── MosparoPluginTest.php
│   └── bootstrap.php      # Test bootstrap
├── i18n/                  # Translation files
│   ├── de.php             # German translations
│   └── en.php             # English translations
├── snippets/              # Kirby snippets
│   ├── mosparo-field.php  # Widget container snippet
│   └── mosparo-script.php # Script tag snippet
└── docs/                  # Documentation
    ├── CONFIGURATION.md   # Configuration reference
    ├── MIGRATION.md       # Migration guide from other CAPTCHAs
    ├── TROUBLESHOOTING.md # Troubleshooting guide
    └── USAGE.md           # Usage examples
```

## Build and Test Commands

All commands are available via Composer scripts:

```bash
# Run the test suite
composer test

# Run tests with code coverage (HTML report)
composer test-coverage

# Install dependencies
composer install

# Update dependencies
composer update
```

### Manual PHPUnit Commands

```bash
# Run tests with configuration file
./vendor/bin/phpunit --configuration phpunit.xml

# Run specific test file
./vendor/bin/phpunit tests/Guards/MosparoGuardTest.php

# Run with coverage report
./vendor/bin/phpunit --configuration phpunit.xml --coverage-html tests/coverage
```

## Code Style Guidelines

### PHP Standards

1. **Strict Typing**: Always declare strict types at the top of PHP files:
   ```php
   <?php
   declare(strict_types=1);
   ```

2. **Namespace**: Use `Uniform\Mosparo\` as the base namespace.

3. **Class Comments**: Include comprehensive PHPDoc blocks:
   ```php
   /**
    * Brief description
    *
    * Longer description if needed.
    *
    * @package Uniform\Mosparo\Namespace
    * @author Patrick Davidt <hallo@davidt.de>
    * @license MIT
    */
   ```

4. **Type Hints**: Use PHP 8.0+ type hints and return types:
   - Use `?string` for nullable strings
   - Use `array<string>` for typed arrays
   - Use `array<string, mixed>` for associative arrays

5. **Readonly Properties**: Use PHP 8.1 `readonly` for immutable value objects:
   ```php
   public function __construct(
       private readonly ?string $host = null,
       private readonly ?string $uuid = null,
   ) {}
   ```

### File Organization

- One class per file
- File name matches class name
- Directory structure mirrors namespace structure
- Helper functions go in `src/helpers.php`

### Naming Conventions

- **Classes**: PascalCase (e.g., `MosparoGuard`, `ConfigFactory`)
- **Methods**: camelCase (e.g., `perform()`, `isConfigured()`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `DEFAULT_IGNORED_FIELDS`)
- **Variables**: camelCase
- **Private properties**: prefixed with `private` (not underscore)

## Testing Instructions

### Test Structure

Tests follow the PHPUnit 10+ structure with Mockery for mocking:

```php
<?php
declare(strict_types=1);

namespace Uniform\Mosparo\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;

final class MyTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function testSomething(): void
    {
        // Test implementation
    }
}
```

### Mocking Patterns

The tests use Mockery for mocking Kirby's App class and Mosparo API client:

```php
// Mock Kirby App with configuration
$mockApp = Mockery::mock('alias:' . App::class);
$mockApp->shouldReceive('instance')->andReturn($mockApp);
$mockApp->shouldReceive('option')->andReturnUsing(function ($key) {
    return $config[$key] ?? null;
});

// Mock Mosparo API client
$mockClient = Mockery::mock('overload:' . Client::class);
$mockClient->shouldReceive('verifySubmission')->andReturn($mockResult);
```

### Test Coverage Areas

- **Configuration**: Config creation, validation, factory methods
- **Guard**: Token extraction, API verification, error handling
- **Security**: Bypass protection, ignored fields, token handling
- **Widget**: HTML rendering, data attributes, script generation
- **Plugin**: Registration, dependency checks

### Running Tests in CI

The test suite is configured to:
- Fail on risky tests
- Fail on warnings
- Execute in depends,defects order
- Generate HTML and text coverage reports

## Security Considerations

### Private Key Protection

**CRITICAL**: The Mosparo private key must NEVER be exposed:

1. **Never** output to frontend (HTML, JavaScript, JSON)
2. **Never** log to error logs or debug output
3. **Never** commit to version control
4. Use environment variables or Kirby secrets for storage:
   ```php
   'getkirby-uniform.mosparo.privateKey' => getenv('MOSPARO_PRIVATE_KEY'),
   ```

### Ignored Fields

Sensitive form fields are automatically excluded from Mosparo verification:

- Default: `['password', 'password_confirm', 'csrf_token']`
- Configurable via `getkirby-uniform.mosparo.ignoredFields`
- Never send passwords, credit cards, or personal data to Mosparo

### Error Handling

- Error messages use translation keys (not raw messages)
- API errors are logged without exposing private keys
- Failed verification uses generic error messages to prevent information leakage

### Bypass Protection

The `VerificationService` includes bypass protection via `verifyRequiredFields()`:
- Checks that Mosparo actually verified all required fields
- Prevents bots from submitting without completing the widget
- Validates field status from API response

## Configuration System

### Option Prefix

All configuration options use the prefix: `getkirby-uniform.mosparo.`

### Required Options

```php
'getkirby-uniform.mosparo.host'        // string: Mosparo instance URL
'getkirby-uniform.mosparo.uuid'        // string: Project UUID
'getkirby-uniform.mosparo.publicKey'   // string: Public key (frontend-safe)
'getkirby-uniform.mosparo.privateKey'  // string: Private key (server-only)
```

### Optional Options

```php
'getkirby-uniform.mosparo.ignoredFields' // array: Fields to exclude (default: passwords, CSRF)
'getkirby-uniform.mosparo.cssUrl'      // string|null: Custom CSS URL
'getkirby-uniform.mosparo.debug'       // bool: Debug mode (default: false)
```

### Configuration Factory

Always use `ConfigFactory` to create Config instances:

```php
// From Kirby options
$config = ConfigFactory::fromKirbyOptions();

// From array (useful for testing)
$config = ConfigFactory::create([
    'host' => 'https://example.com',
    'uuid' => 'test-uuid',
    // ...
]);
```

## Architecture Patterns

### Guard Pattern

`MosparoGuard` extends `Uniform\Guards\Guard`:
- Implements `perform()` method for verification
- Calls `reject()` on validation failure
- Integrates with Uniform's form validation flow

### Value Object Pattern

`Config` is an immutable value object:
- All properties are `readonly`
- No setters - create new instance for changes
- Type-safe with null-safe getters

### Factory Pattern

`ConfigFactory` creates Config instances:
- Handles option prefixing (`getkirby-uniform.mosparo.`)
- Type coercion from Kirby options
- Default value application

### Helper Functions

Global helper functions for templates:
- `mosparo_field(array $options = []): string` - Render widget HTML
- `mosparo_script(array $options = []): string` - Render script tag

These silently fail (return empty string) if not configured, for production safety.

## Translation Keys

Error messages use these translation keys:

```php
'mosparo.error.not_configured'       // Plugin not configured
'mosparo.error.tokens_missing'       // Missing Mosparo tokens
'mosparo.error.verification_failed'  // Spam check failed
'mosparo.error.api_error'            // API connection error
'mosparo.error.bypass_detected'      // Tampering detected
'mosparo.error.invalid_token'        // Invalid token format
```

Add translations in `i18n/{lang}.php` files.

## Common Development Tasks

### Adding a New Configuration Option

1. Add to `Config` constructor property
2. Add getter method in `Config`
3. Add factory method in `ConfigFactory`
4. Add default in `MosparoPlugin::register()`
5. Add to example config in `config/options.php`
6. Add tests in `tests/Config/ConfigTest.php`

### Adding a New Guard Feature

1. Modify `MosparoGuard::perform()`
2. Add error constant to `VerificationException` if needed
3. Add translation key to i18n files
4. Add comprehensive tests with mocked API client
5. Update documentation

### Adding Widget Customization

1. Modify `WidgetRenderer` or helper functions
2. Support via data attributes (`data-mosparo-*`)
3. Update snippets if needed
4. Add tests for HTML output

## Debugging

Enable debug mode in configuration:

```php
'getkirby-uniform.mosparo.debug' => true,
```

This enables detailed logging (check PHP error logs).

**Remember**: Always disable debug mode in production!

## Dependencies

The plugin has these external dependencies:

- `mzur/kirby-uniform` - Required for Guard functionality
- `mosparo/php-api-client` - Required for API communication

Both are declared in `composer.json` and automatically loaded.

## Release Checklist

When preparing a release:

1. Update version constant in `MosparoPlugin::VERSION`
2. Update `CHANGELOG.md` with release date
3. Run full test suite: `composer test`
4. Verify code coverage: `composer test-coverage`
5. Test with actual Mosparo instance
6. Tag release in Git

## Useful Resources

- [Mosparo Documentation](https://mosparo.io/docs/)
- [Kirby Uniform Documentation](https://kirby-uniform.readthedocs.io/)
- [Kirby Plugin Documentation](https://getkirby.com/docs/reference/plugins)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Mockery Documentation](https://docs.mockery.io/)
