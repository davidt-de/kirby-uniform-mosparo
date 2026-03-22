# Architecture Research: Kirby Uniform Mosparo Plugin

**Domain:** Kirby CMS Plugin - CAPTCHA/Spam Protection Integration  
**Researched:** 2026-03-06  
**Confidence:** HIGH

## Standard Architecture

### System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    Kirby Plugin Layer                            │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │   index.php     │  │  helpers.php    │  │  i18n/*.php     │ │
│  │  (Registration) │  │ (Template Func) │  │ (Translations)  │ │
│  └────────┬────────┘  └────────┬────────┘  └─────────────────┘ │
│           │                    │                                │
├───────────┴────────────────────┴────────────────────────────────┤
│                    Uniform Integration Layer                     │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    MosparoGuard                         │   │
│  │              (extends Uniform\Guards\Guard)              │   │
│  │  ┌─────────────────────────────────────────────────┐    │   │
│  │  │  perform(): Validates submission via API        │    │   │
│  │  │  reject(): Throws PerformerException on failure │    │   │
│  │  └─────────────────────────────────────────────────┘    │   │
│  └─────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│                    External Service Layer                        │
│  ┌──────────────────────┐  ┌────────────────────────────────┐  │
│  │  mosparo PHP Client  │  │      Mosparo Instance          │  │
│  │  (Composer Package)  │  │    (Self-hosted/Cloud)         │  │
│  └──────────────────────┘  └────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Typical Implementation |
|-----------|----------------|------------------------|
| `index.php` | Plugin registration, options, autoloading | `Kirby::plugin()` with options array |
| `MosparoGuard.php` | CAPTCHA validation via Mosparo API | Extends `Guard`, implements `perform()` |
| `helpers.php` | Template helper functions for frontend | Global functions: `mosparoField()`, `mosparoScript()` |
| `i18n/*.php` | Translation strings for error messages | PHP arrays with keyed messages |
| `composer.json` | Dependencies (mosparo API client) | Require `mosparo/php-api-client` |

## Recommended Project Structure

```
kirby-uniform-mosparo/
├── index.php                 # Plugin entry point, registration
├── composer.json             # Dependencies and metadata
├── src/
│   ├── Guards/
│   │   └── MosparoGuard.php  # Main guard implementation
│   └── helpers.php           # Template helper functions
└── i18n/
    ├── en.php                # English translations
    └── de.php                # German translations
```

### Structure Rationale

- **`index.php`:** Standard Kirby plugin entry point. Registers plugin with `Kirby::plugin()`, defines default options, loads helpers and translations.
- **`src/Guards/`:** Follows Uniform's convention for guard classes. Must extend `Uniform\Guards\Guard` base class.
- **`src/helpers.php`:** Provides template functions for rendering the Mosparo widget and loading required JavaScript. Uses global functions for easy template access.
- **`i18n/`:** Kirby's standard location for translation files. Keys should follow pattern: `uniform-mosparo-[message]`.

## Uniform Guard Architecture

### Guard Base Class

All Uniform guards extend `Uniform\Guards\Guard`:

```php
<?php
namespace Uniform\Guards;

class Guard extends Performer
{
    public function perform()
    {
        $this->reject();
    }

    protected function reject($message = null, $key = null)
    {
        $message = $message ?: static::class.' rejected the request.';
        $key = $key ?: static::class;
        throw new PerformerException($message, $key);
    }
}
```

**Key Requirements:**
1. Extend `Uniform\Guards\Guard`
2. Override `perform()` method
3. Call `$this->reject($message, $key)` on validation failure
4. Optionally call `$this->form->forget($fieldName)` to remove CAPTCHA data from form

### Mosparo Guard Implementation Pattern

```php
<?php
namespace Uniform\Guards;

use Mosparo\ApiClient\Client;
use Uniform\Exceptions\Exception;

class MosparoGuard extends Guard
{
    const FIELD_SUBMIT_TOKEN = '_mosparo_submitToken';
    const FIELD_VALIDATION_TOKEN = '_mosparo_validationToken';

    public function perform()
    {
        $submitToken = kirby()->request()->get(self::FIELD_SUBMIT_TOKEN);
        $validationToken = kirby()->request()->get(self::FIELD_VALIDATION_TOKEN);

        if (empty($submitToken) || empty($validationToken)) {
            $this->reject(t('uniform-mosparo-empty'), self::FIELD_SUBMIT_TOKEN);
        }

        $host = option('uniform-mosparo.host');
        $publicKey = option('uniform-mosparo.publicKey');
        $privateKey = option('uniform-mosparo.privateKey');

        if (empty($host) || empty($publicKey) || empty($privateKey)) {
            throw new Exception('Mosparo configuration incomplete');
        }

        $client = new Client($host, $publicKey, $privateKey);
        $result = $client->verifySubmission(
            $this->form->data(),
            $submitToken,
            $validationToken
        );

        if (!$result->isSubmittable()) {
            $this->reject(t('uniform-mosparo-invalid'), self::FIELD_SUBMIT_TOKEN);
        }

        // Clean up Mosparo fields from form data
        $this->form->forget(self::FIELD_SUBMIT_TOKEN);
        $this->form->forget(self::FIELD_VALIDATION_TOKEN);
    }
}
```

## Data Flow

### Form Submission Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                         User Browser                             │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │  1. User fills form with Mosparo widget                     │  │
│  │  2. Mosparo JS validates client-side                        │  │
│  │  3. Form submits with tokens (_mosparo_* fields)            │  │
│  └─────────────────────┬──────────────────────────────────────┘  │
└────────────────────────┼─────────────────────────────────────────┘
                         │ POST request
                         ↓
┌──────────────────────────────────────────────────────────────────┐
│                     Kirby Controller                             │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │  $form = new Form([...]);                                   │  │
│  │  $form->mosparoGuard()  ← Magic method calls Guard          │  │
│  └─────────────────────┬──────────────────────────────────────┘  │
└────────────────────────┼─────────────────────────────────────────┘
                         │
                         ↓
┌──────────────────────────────────────────────────────────────────┐
│                     MosparoGuard::perform()                      │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │  4. Extract tokens from request                             │  │
│  │  5. Validate tokens with Mosparo API                        │  │
│  │  6. if valid → continue to actions                          │  │
│  │     if invalid → reject() → PerformerException              │  │
│  └─────────────────────┬──────────────────────────────────────┘  │
└────────────────────────┼─────────────────────────────────────────┘
                         │
              ┌──────────┴──────────┐
              │                     │
              ▼                     ▼
┌─────────────────────┐   ┌─────────────────────┐
│     SUCCESS         │   │      FAILURE        │
│  Continue to        │   │  Form::fail()       │
│  emailAction() etc. │   │  Redirect with      │
│                     │   │  error message      │
└─────────────────────┘   └─────────────────────┘
```

### Magic Method Resolution

Uniform's `Form` class provides magic methods for guards:

```php
// In controller:
$form->mosparoGuard();

// Resolves to:
$form->guard('\Uniform\Guards\MosparoGuard');
```

**Naming Convention:**
- Method suffix: `Guard`
- Class prefix: Same as method name (without suffix)
- Full class: `\Uniform\Guards\[Name]Guard`

## Template Integration

### Required Template Components

```php
<!-- 1. Mosparo container -->
<div id="mosparo-box"></div>

<!-- 2. CSRF protection (Kirby native) -->
<?= csrf_field() ?>

<!-- 3. Mosparo hidden fields (auto-injected by JS) -->
<!-- _mosparo_submitToken, _mosparo_validationToken -->
```

### Helper Functions Pattern

```php
<?php
// src/helpers.php

if (!function_exists('mosparoField')) {
    function mosparoField(): string
    {
        $host = option('uniform-mosparo.host');
        $uuid = option('uniform-mosparo.uuid');
        $publicKey = option('uniform-mosparo.publicKey');
        
        if (empty($host) || empty($uuid) || empty($publicKey)) {
            throw new Exception('Mosparo configuration incomplete');
        }
        
        return '<div id="mosparo-box" 
            data-host="' . $host . '" 
            data-uuid="' . $uuid . '" 
            data-public-key="' . $publicKey . '"></div>';
    }
}

if (!function_exists('mosparoScript')) {
    function mosparoScript(): string
    {
        $host = option('uniform-mosparo.host');
        return '<script src="' . $host . '/build/mosparo-frontend.js" defer></script>';
    }
}
```

## Configuration Schema

```php
// config.php
return [
    'uniform-mosparo.host' => 'https://mosparo.example.com',
    'uniform-mosparo.uuid' => 'project-uuid-here',
    'uniform-mosparo.publicKey' => 'public-key-here',
    'uniform-mosparo.privateKey' => 'private-key-here',
    'uniform-mosparo.designMode' => 'visible', // or 'invisible'
];
```

## Build Order (Phase Dependencies)

```
Phase 1: Core Guard Implementation
├── index.php (plugin registration)
├── src/Guards/MosparoGuard.php
└── composer.json (add mosparo/php-api-client)
    ↓
Phase 2: Frontend Integration
├── src/helpers.php (mosparoField, mosparoScript)
└── Basic template usage examples
    ↓
Phase 3: Configuration & Error Handling
├── i18n/en.php (translation strings)
├── Configuration validation
└── Error message display
    ↓
Phase 4: Testing & Documentation
├── Unit tests for Guard
├── Integration test examples
└── Documentation
```

**Dependency Notes:**
- Phase 2 depends on Phase 1 (helpers need guard to exist)
- Phase 3 depends on Phase 2 (error messages need frontend context)
- Mosparo PHP client is required for Phase 1

## Anti-Patterns to Avoid

### Anti-Pattern 1: Direct API Calls Without Client

**What people do:** Implement raw HTTP calls to Mosparo API instead of using the official PHP client.

**Why it's wrong:** 
- Duplicate signature calculation logic
- Risk of security vulnerabilities in HMAC implementation
- Maintenance burden when API changes

**Do this instead:** Use `mosparo/php-api-client` Composer package which handles:
- Request signing with HMAC SHA256
- Form data preparation and hashing
- Response validation

### Anti-Pattern 2: Client-Side Only Validation

**What people do:** Rely only on JavaScript validation without server-side verification.

**Why it's wrong:**
- Easily bypassed by bots
- Breaks without JavaScript
- No actual spam protection

**Do this instead:** Always verify tokens server-side in `MosparoGuard::perform()`.

### Anti-Pattern 3: Hardcoded Configuration

**What people do:** Embed Mosparo credentials directly in guard class.

**Why it's wrong:**
- Credentials in version control
- No environment-specific config
- Violates 12-factor app principles

**Do this instead:** Use Kirby's `option()` system with config.php.

## Integration Points

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| Mosparo API | PHP API Client | Use official `mosparo/php-api-client` package |
| Mosparo Frontend | JavaScript | Load from `/build/mosparo-frontend.js` on Mosparo host |
| Kirby Core | Plugin API | Register via `Kirby::plugin()` |
| Uniform | Guard Interface | Extend `Uniform\Guards\Guard` |

### Internal Boundaries

| Boundary | Communication | Notes |
|----------|---------------|-------|
| Template ↔ Guard | Helper functions | Global functions provide clean template API |
| Guard ↔ Mosparo API | PHP Client | Encapsulates authentication and verification |
| Plugin ↔ User Config | `option()` API | Standard Kirby configuration pattern |

## Sources

- [Kirby Uniform Plugin](https://github.com/mzur/kirby-uniform) - Core architecture
- [reCAPTCHA Guard](https://github.com/eXpl0it3r/kirby-uniform-recaptcha) - Plugin structure reference
- [hCaptcha Guard](https://github.com/lukasleitsch/kirby-uniform-hcaptcha) - Alternative implementation
- [Kirby Plugin Docs](https://getkirby.com/docs/reference/plugins) - Extension points
- [Mosparo Integration](https://documentation.mosparo.io/docs/integration/custom) - API patterns
- [Mosparo PHP Client](https://github.com/mosparo/php-api-client) - Verification implementation

---
*Architecture research for: Kirby Uniform Mosparo Plugin*  
*Researched: 2026-03-06*
