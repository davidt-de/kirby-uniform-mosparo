# Technology Stack: Kirby Uniform Mosparo Plugin

**Project:** Kirby Uniform Mosparo Plugin  
**Domain:** Kirby CMS Form Protection Plugin  
**Researched:** March 6, 2026  
**Confidence:** HIGH

## Executive Summary

Based on comprehensive research of the Kirby Uniform ecosystem and Mosparo integration patterns, this document outlines the definitive technical stack for building a CAPTCHA-style spam protection plugin. The stack prioritizes compatibility with Kirby 3.5+ through Kirby 5, leverages existing proven patterns from reference implementations, and utilizes the official Mosparo PHP API client for reliable server-side verification.

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| **PHP** | 8.2+ (8.3 recommended) | Server-side language | Kirby 5 requires PHP 8.2-8.4; 8.3 is the recommended "sweet spot" for performance and stability. Supporting 8.2+ ensures compatibility with Kirby 4 and 5 while leveraging modern PHP features (match expressions, readonly properties, fiber support). |
| **Kirby CMS** | ^3.5 \\| ^4.0 \\| ^5.0 | CMS Platform | Uniform plugin officially supports these versions. Kirby 3.5+ maintains backward compatibility while Kirby 4/5 introduce modern PHP requirements. Targeting all three maximizes plugin adoption. |
| **Uniform Plugin** | ^5.0 | Form handling framework | The de facto standard for Kirby form processing. Provides the Guard API architecture that all CAPTCHA implementations extend. Version 5.x is current and actively maintained (last release Oct 2025). |
| **Mosparo PHP API Client** | ^1.1 | API communication | Official Mosparo-maintained library (v1.1.0, Jan 2024). Handles HMAC-SHA256 signature generation, verification API calls, and response parsing. Prevents reimplementation of cryptographic verification logic. |

### Frontend Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| **Mosparo Frontend JS** | Latest (v1.x) | Client-side protection | Loaded directly from Mosparo instance (`/build/mosparo-frontend.js`). Handles checkbox rendering, validation flow, and token generation. Using the official script ensures compatibility with Mosparo server updates. |
| **Mosparo Frontend CSS** | Latest (v1.x) | Widget styling | Loaded either via `loadCssResource: true` option or manual CSS include. Supports both simple and advanced (CSS variable) integration modes for design flexibility. |

### Composer Dependencies

```json
{
  "require": {
    "php": ">=8.2",
    "getkirby/composer-installer": "^1.2",
    "mosparo/php-api-client": "^1.1"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.0 || ^10.0",
    "getkirby/cms": "^3.5 || ^4.0 || ^5.0"
  }
}
```

### Development Tools

| Tool | Purpose | Configuration |
|------|---------|---------------|
| **PHPUnit** | Unit testing | ^9.0 for PHP 8.0-8.2 compatibility; ^10.0 for PHP 8.2+ only |
| **PHP-CS-Fixer** | Code style | PSR-12 compliant, matches Kirby ecosystem standards |
| **Composer** | Dependency management | Required for autoloading and Kirby plugin installation |

## Architecture Patterns

### Plugin Structure (PSR-4 Autoloading)

Following the established pattern from reCAPTCHA, hCaptcha, and Turnstile implementations:

```
site/plugins/uniform-mosparo/
├── src/
│   ├── MosparoGuard.php          # Main guard implementation
│   └── Mosparo.php               # Helper class (optional)
├── i18n/
│   ├── en.php                    # English translations
│   └── de.php                    # German translations (Mosparo is EU-based)
├── index.php                     # Plugin bootstrap
└── composer.json
```

### Guard Implementation Pattern

All reference implementations follow this pattern:

1. **Guard Class** extends `Uniform\Guards\Guard`
2. **Magic method** registered via Uniform's guard system (`mosparoGuard()`)
3. **Configuration** via Kirby options (`yourname.uniform-mosparo.siteKey`)
4. **Template helpers** for script injection and field rendering

### Mosparo Integration Flow

```
Frontend:
1. Load mosparo-frontend.js from Mosparo host
2. Initialize with UUID, publicKey, and options
3. Mosparo renders checkbox widget
4. On submit: Mosparo validates and injects tokens

Backend:
1. Extract _mosparo_submitToken and _mosparo_validationToken
2. Remove Mosparo-internal fields from form data
3. Use Mosparo\ApiClient\Client to verify submission
4. Guard passes/fails based on isSubmittable() result
```

## API Endpoints and Authentication

### Mosparo API Endpoint

- **URL**: `https://<mosparo-host>/api/v1/verification/verify`
- **Method**: POST
- **Authentication**: Basic Auth (Base64-encoded `publicKey:requestSignature`)

### Authentication Flow

1. **Request Signature**: HMAC-SHA256 of `apiEndpoint + jsonRequestData` using private key
2. **Validation Signature**: HMAC-SHA256 of validation token using private key
3. **Form Signature**: HMAC-SHA256 of JSON-encoded hashed form data using private key
4. **Verification**: Mosparo responds with `valid` boolean and `verificationSignature` for client-side verification

### Required Configuration

```php
// config.php
return [
  'yourname.uniform-mosparo.host' => 'https://mosparo.example.com',
  'yourname.uniform-mosparo.uuid' => 'project-uuid-from-mosparo',
  'yourname.uniform-mosparo.publicKey' => 'public-key-from-mosparo',
  'yourname.uniform-mosparo.privateKey' => 'private-key-from-mosparo',
  'yourname.uniform-mosparo.theme' => 'auto', // auto, light, dark
];
```

## Version Compatibility Matrix

| Component | Kirby 3.5 | Kirby 4 | Kirby 5 | Notes |
|-----------|-----------|---------|---------|-------|
| PHP 8.0 | ✓ | ✓ | ✗ | Kirby 5 requires 8.2+ |
| PHP 8.1 | ✓ | ✓ | ✗ | |
| PHP 8.2 | ✓ | ✓ | ✓ | Minimum for Kirby 5 |
| PHP 8.3 | ✓ | ✓ | ✓ | **Recommended** |
| PHP 8.4 | ? | ? | ✓ | Test for deprecation warnings |
| Uniform 5.x | ✓ | ✓ | ✓ | |
| Mosparo API 1.x | ✓ | ✓ | ✓ | PHP 7.4+ required by client |

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| **Manual verification implementation** | HMAC-SHA256 signature generation is error-prone; Mosparo's verification involves 11 steps including hash sorting and multiple signatures | Official `mosparo/php-api-client` library |
| **Kirby 3.0-3.4** | Uniform 5.x requires Kirby 3.5+ for API compatibility | Kirby 3.5+ or upgrade path documented |
| **Custom JavaScript widget** | Bypasses Mosparo's security updates and validation logic | Official `mosparo-frontend.js` from Mosparo host |
| **Synchronous API calls** | Blocking the form submission thread degrades UX | Mosparo's async validation with token injection |
| **Storing privateKey in frontend** | Private key is used for request signing; exposure allows forged verifications | Only publicKey in frontend; privateKey server-side only |
| **PHP 7.4** | End-of-life since November 2022; no security updates | PHP 8.2+ (aligned with Kirby 5) |

## Alternatives Considered

| Approach | Why Not Chosen | When It Makes Sense |
|----------|----------------|---------------------|
| **Custom HTTP client instead of Mosparo PHP API Client** | Adds maintenance burden for cryptographic verification logic; 400+ lines of verification code vs. 5-line API call | If you need to minimize Composer dependencies in embedded environments |
| **Supporting Kirby 2** | Kirby 2 is end-of-life; Uniform for Kirby 2 is in maintenance mode only | Legacy site maintenance only (not recommended for new plugins) |
| **JavaScript-only validation** | Bypassable by disabling JS; doesn't protect against direct API spam | Never for production spam protection |
| **Self-hosted Mosparo fallback** | Outside scope; plugin assumes existing Mosparo instance | Enterprise deployments with dedicated Mosparo infrastructure |

## Installation

### For End Users

```bash
composer require yourname/kirby-uniform-mosparo
```

### For Development

```bash
# Clone and install dependencies
git clone https://github.com/yourname/kirby-uniform-mosparo.git
cd kirby-uniform-mosparo
composer install

# Run tests
./vendor/bin/phpunit
```

## Confidence Assessment

| Component | Confidence | Rationale |
|-----------|------------|-----------|
| PHP 8.2+ requirement | HIGH | Official Kirby docs; verified March 2026 |
| Uniform Guard API | HIGH | Three reference implementations with identical patterns; official docs |
| Mosparo API Client | HIGH | Official Mosparo project; v1.1.0 stable since Jan 2024 |
| Frontend integration | HIGH | Mosparo documentation; standard script injection pattern |
| Kirby 5 compatibility | MEDIUM-HIGH | Uniform dev dependency includes 5.0.0-rc.1; no breaking changes expected |

## Sources

- [Kirby Uniform Repository](https://github.com/mzur/kirby-uniform) - Plugin structure and Guard API
- [Kirby Uniform reCAPTCHA](https://github.com/eXpl0it3r/kirby-uniform-recaptcha) - Reference implementation (Kirby 3/4, Google reCAPTCHA v3)
- [Kirby Uniform hCaptcha](https://github.com/lukasleitsch/kirby-uniform-hcaptcha) - Reference implementation (Kirby 3, hCaptcha)
- [Kirby Uniform Turnstile](https://github.com/anselmh/kirby-uniform-turnstile) - Reference implementation (Kirby 3/4, Cloudflare Turnstile)
- [Mosparo Custom Integration Docs](https://documentation.mosparo.io/docs/integration/custom) - Frontend integration and API specification
- [Mosparo PHP API Client](https://github.com/mosparo/php-api-client) - Official server-side verification library
- [Kirby Requirements](https://getkirby.com/docs/guide/quickstart) - PHP version requirements (8.2-8.4 for Kirby 5)
- [Uniform composer.json](https://github.com/mzur/kirby-uniform/blob/master/composer.json) - Version constraints and dependencies

---

*Stack research for: Kirby Uniform Mosparo Plugin*  
*Researched: March 6, 2026*  
*Confidence: HIGH*
