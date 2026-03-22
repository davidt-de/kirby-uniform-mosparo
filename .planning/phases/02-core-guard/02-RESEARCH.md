# Phase 2: Core Guard - Research

**Researched:** 2026-03-06
**Domain:** Mosparo verification API, Kirby Uniform Guards, Server-side validation
**Confidence:** HIGH

## Summary

Phase 2 implements the core Mosparo Guard with server-side verification. The Mosparo PHP API Client (v1.1.0) provides a complete verification flow via `Client::verifySubmission()`, which handles the 11-step verification process internally including HMAC signature generation, token validation, and field verification.

The Guard must extract Mosparo tokens from POST data, verify submissions against the Mosparo API, handle ignored fields (checkboxes, passwords, hidden fields), and prevent bot bypass attacks by checking that required fields were actually verified. Configuration is handled through Kirby's options system with secure storage of private keys.

**Primary recommendation:** Use the official `mosparo/php-api-client` library's `verifySubmission()` method with proper form data preparation. Implement bypass protection by checking verifiedFields from the API response. Handle all API exceptions gracefully with translated error messages.

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| mosparo/php-api-client | ^1.0 | Mosparo API communication | Official library, handles HMAC signing, token validation, and verification flow |
| GuzzleHTTP | ^7.0 (transitive) | HTTP client for API requests | Industry standard, used internally by Mosparo client |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Mockery | ^1.6 | Mock Mosparo API client in tests | All verification tests should mock the Client |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| mosparo/php-api-client | Custom cURL implementation | Requires implementing HMAC-SHA256 signing, signature verification, and token management - complex and error-prone |

**Installation:**
```bash
# Already included in composer.json from Phase 1
composer require mosparo/php-api-client
```

## Architecture Patterns

### Recommended Project Structure
```
src/
├── Guards/
│   └── MosparoGuard.php          # Main guard implementation
├── Exception/
│   └── VerificationException.php # Custom exception for validation failures
├── Config/
│   └── ConfigFactory.php         # Kirby options to Mosparo config mapping
└── Validation/
    └── VerificationService.php   # Wrapper around Mosparo API client
```

### Pattern 1: Guard Implementation Pattern
**What:** Extend Uniform\Guards\Guard and implement perform() method
**When to use:** All form validation guards in Uniform
**Example:**
```php
// Source: Uniform Guard pattern (from existing MosparoGuard.php stub)
class MosparoGuard extends Guard
{
    public function perform(): void
    {
        // Extract tokens from form data
        $submitToken = $this->data['_mosparo_submitToken'] ?? null;
        $validationToken = $this->data['_mosparo_validationToken'] ?? null;
        
        // Verify submission via Mosparo API
        $result = $this->verify($submitToken, $validationToken);
        
        if (!$result->isSubmittable()) {
            $this->reject($result);
        }
    }
}
```

### Pattern 2: Form Data Preparation
**What:** Remove Mosparo tokens and ignored fields before verification
**When to use:** Before calling Mosparo API
**Example:**
```php
// Source: Mosparo PHP API Client RequestHelper.php lines 90-120
public function prepareFormData(array $formData): array
{
    // Remove Mosparo tokens (done automatically by cleanupFormData)
    // Remove ignored field types
    $ignoredFields = $this->config->getIgnoredFields();
    foreach ($ignoredFields as $field) {
        unset($formData[$field]);
    }
    
    return $formData;
}
```

### Pattern 3: Bypass Protection via verifiedFields
**What:** Check that required fields were actually verified by Mosparo
**When to use:** Prevent bot attacks that bypass frontend widget
**Example:**
```php
// Source: Mosparo PHP API Client VerificationResult.php
$result = $client->verifySubmission($formData, $submitToken, $validationToken);
$verifiedFields = $result->getVerifiedFields();

// Check required fields exist in verified fields
foreach ($requiredFields as $field) {
    if (!isset($verifiedFields[$field]) || 
        $verifiedFields[$field] !== VerificationResult::FIELD_VALID) {
        // Field wasn't verified - likely a bot bypass attempt
        throw new VerificationException('verification_failed');
    }
}
```

### Pattern 4: Graceful Error Handling
**What:** Catch API exceptions and convert to user-friendly error messages
**When to use:** All API communication
**Example:**
```php
try {
    $result = $client->verifySubmission($formData, $submitToken, $validationToken);
} catch (\Mosparo\ApiClient\Exception $e) {
    // Log detailed error for admin (without exposing private key)
    $this->logger->error('Mosparo API error: ' . $e->getMessage());
    
    // Show generic message to user
    $this->fail('mosparo.error.verification_failed');
}
```

### Anti-Patterns to Avoid
- **Don't expose privateKey in frontend:** Only publicKey and UUID should be in JavaScript
- **Don't log full API responses:** May contain sensitive data
- **Don't trust frontend-only validation:** Always verify server-side
- **Don't skip verifiedFields check:** This is the bypass protection mechanism

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| HMAC signature generation | Custom hash functions | Mosparo\ApiClient\RequestHelper | Handles edge cases like array fields, line ending normalization, JSON encoding quirks |
| Token extraction | Manual $_POST parsing | Client auto-extraction (lines 76-82) | Client automatically finds tokens in form data |
| API request signing | Custom auth headers | Client::verifySubmission() | Multi-step signature process (formSignature → validationSignature → verificationSignature) |
| SSL/TLS configuration | curl_setopt() | Guzzle client arguments | Production-ready HTTP handling with timeouts, retries, proxy support |

**Key insight:** The Mosparo verification involves 4 different HMAC signatures (form, validation, verification, request) with specific concatenation rules. Getting any step wrong breaks security. The official client is battle-tested and handles all edge cases.

## Common Pitfalls

### Pitfall 1: Missing verifiedFields Check
**What goes wrong:** Bots bypass frontend widget, submit form directly with fake tokens, passes validation
**Why it happens:** Developer only checks `isSubmittable()` without verifying required fields were actually checked by Mosparo
**How to avoid:** Always check `getVerifiedFields()` contains your required form fields with `FIELD_VALID` status
**Warning signs:** Spam submissions getting through despite tokens being present

### Pitfall 2: Ignored Fields Not Filtered
**What goes wrong:** Checkboxes, hidden fields, passwords cause verification failures because Mosparo can't verify them
**Why it happens:** Mosparo JavaScript doesn't process these field types, so they won't be in verifiedFields
**How to avoid:** Remove ignored field types from formData before calling verifySubmission()
**Warning signs:** Legitimate submissions failing validation for checkbox/password fields

### Pitfall 3: Private Key Exposure
**What goes wrong:** Private key leaks to frontend JavaScript or error logs
**Why it happens:** Configuration object passed to template, or exception messages include full config
**How to avoid:** Only expose host, uuid, publicKey to frontend. Catch exceptions and log sanitized messages.
**Warning signs:** Private key visible in browser dev tools or log files

### Pitfall 4: API Exception Leakage
**What goes wrong:** Raw Mosparo exceptions shown to users with technical details
**Why it happens:** Not catching `\Mosparo\ApiClient\Exception` or re-throwing without translation
**How to avoid:** Wrap all API calls in try-catch, return translated error keys to frontend
**Warning signs:** Users see "GuzzleHTTP error cURL timeout" instead of user-friendly message

### Pitfall 5: Token Name Mismatch
**What goes wrong:** Frontend sends `_mosparo_submitToken` but backend looks for different name
**Why it happens:** Inconsistent naming between frontend integration and backend extraction
**How to avoid:** Use Mosparo client's auto-extraction (it looks for `_mosparo_submitToken` and `_mosparo_validationToken` by default)
**Warning signs:** "Submit or validation token not available" errors on all submissions

## Code Examples

### Example 1: Complete Guard Implementation
```php
// Source: Based on Mosparo PHP API Client README and Uniform patterns
class MosparoGuard extends Guard
{
    public function perform(): void
    {
        $config = $this->getMosparoConfig();
        
        if (!$config->isConfigured()) {
            throw new \RuntimeException('Mosparo not configured');
        }
        
        $client = new Client(
            $config->getHost(),
            $config->getPublicKey(),
            $config->getPrivateKey()
        );
        
        // Prepare form data (remove ignored fields)
        $formData = $this->prepareFormData($this->data);
        
        try {
            $result = $client->verifySubmission($formData);
        } catch (\Mosparo\ApiClient\Exception $e) {
            $this->fail('mosparo.error.api_error');
            return;
        }
        
        if (!$result->isSubmittable()) {
            $this->fail('mosparo.error.verification_failed');
            return;
        }
        
        // Bypass protection: verify required fields were checked
        if (!$this->verifyRequiredFields($result)) {
            $this->fail('mosparo.error.bypass_detected');
            return;
        }
    }
}
```

### Example 2: Configuration via Kirby Options
```php
// Source: Kirby plugin options pattern (from existing MosparoPlugin.php)
// config/config.php or site/config/config.php
return [
    'getkirby-uniform.mosparo.host' => 'https://mosparo.example.com',
    'getkirby-uniform.mosparo.publicKey' => 'your-public-key',
    'getkirby-uniform.mosparo.privateKey' => 'your-private-key',
    'getkirby-uniform.mosparo.uuid' => 'your-project-uuid',
    'getkirby-uniform.mosparo.ignoredFields' => ['password', 'csrf_token'],
];
```

### Example 3: Testing with Mockery
```php
// Source: PHPUnit + Mockery pattern
public function testValidSubmissionPasses()
{
    $mockClient = Mockery::mock(Client::class);
    $mockResult = Mockery::mock(VerificationResult::class);
    
    $mockResult->shouldReceive('isSubmittable')->andReturn(true);
    $mockResult->shouldReceive('getVerifiedFields')->andReturn([
        'email' => VerificationResult::FIELD_VALID,
        'message' => VerificationResult::FIELD_VALID,
    ]);
    
    $mockClient->shouldReceive('verifySubmission')
        ->once()
        ->andReturn($mockResult);
    
    // Inject mock and test...
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Manual cURL requests | Official PHP API Client v1.0 | 2021 | Security: proper HMAC implementation |
| validateSubmission() | verifySubmission() | v1.0.2 (2022) | API naming consistency |
| Single signature | Multi-signature chain | v1.0 | Security: replay attack prevention |

**Deprecated/outdated:**
- `Client::validateSubmission()`: Deprecated in v1.0.2, use `verifySubmission()` instead
- Empty array JSON encoding: Client now replaces `[]` with `{}` for consistent hashing

## Open Questions

1. **What fields should be ignored by default?**
   - What we know: Checkboxes, hidden fields, passwords can't be verified by Mosparo JS
   - What's unclear: Should CSRF tokens be ignored? File upload fields?
   - Recommendation: Start with ['password', 'password_confirm', 'csrf_token'], make configurable

2. **How to handle API timeouts?**
   - What we know: Guzzle can timeout, Mosparo client throws Exception
   - What's unclear: Should timeout = fail closed (reject) or fail open (allow)?
   - Recommendation: Fail closed (reject submission) - security over availability

3. **Should we cache Mosparo Client instances?**
   - What we know: Client is stateless, lightweight
   - What's unclear: Performance impact of creating new Client per request
   - Recommendation: Create fresh instance in perform() - simpler, no shared state issues

## Validation Architecture

> Note: workflow.nyquist_validation is not enabled in config.json (set to false or missing), so this section is omitted per instructions.

## Sources

### Primary (HIGH confidence)
- `vendor/mosparo/php-api-client/src/Client.php` - Complete API implementation with verifySubmission() method, HMAC signing flow
- `vendor/mosparo/php-api-client/src/VerificationResult.php` - Result object with isSubmittable(), isValid(), getVerifiedFields()
- `vendor/mosparo/php-api-client/src/RequestHelper.php` - Form data preparation, cleanup, HMAC generation
- `vendor/mosparo/php-api-client/src/Exception.php` - Exception class for error handling
- `vendor/mosparo/php-api-client/README.md` - Usage examples and API documentation

### Secondary (MEDIUM confidence)
- `src/Guards/MosparoGuard.php` (existing stub) - Uniform Guard pattern, perform() method signature
- `src/MosparoPlugin.php` - Kirby options structure, plugin registration pattern

### Tertiary (LOW confidence)
- None - all critical information verified against source code

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Official Mosparo client is the only supported library
- Architecture: HIGH - Clear patterns from existing codebase and Mosparo docs
- Pitfalls: HIGH - All pitfalls verified against actual API client source code

**Research date:** 2026-03-06
**Valid until:** 2026-06-06 (Mosparo API client is stable, v1.1.0 released Jan 2024)

---

<user_constraints>
## User Constraints (from Phase 1 CONTEXT.md)

### Locked Decisions
- Directory structure: src/ for PHP classes, tests/ for PHPUnit tests
- Composer: Package name format vendor/plugin-name, type: kirby-plugin
- Kirby compatibility: 4.x+ (PHP 8.0+)
- Testing: Mockery or PHPUnit mocks for Kirby classes
- Registration: index.php with Kirby::plugin()

### OpenCode's Discretion
- Exact PSR-4 namespace naming
- Specific PHPUnit configuration details
- PHP-CS-Fixer rule set selection
- Static analysis level for PHPStan

### Deferred Ideas (OUT OF SCOPE)
- Uniform Actions - Add in Phase 3
- Advanced template helpers - Full widget integration deferred to Phase 3
- CI/CD setup - Consider in Phase 4
- Multi-language support infrastructure - Defer i18n setup to Phase 3
</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| CONFIG-01 | Mosparo host URL configurable via Kirby options | Pattern documented in Architecture section, example in MosparoPlugin.php |
| CONFIG-02 | Project UUID and public key for frontend | Stored in Kirby options, publicKey exposed to frontend |
| CONFIG-03 | Private key for server-side (secure) | Stored in Kirby options, NEVER exposed to frontend or logs |
| CONFIG-04 | Validation mode selection (checkbox/invisible) | Controlled via Mosparo project settings, frontend initialization |
| CONFIG-05 | Optional: Custom CSS for widget styling | CSS path configurable, loaded via frontend snippet |
| GUARD-01 | Extend `Uniform\Guards\Guard` base class | Pattern from existing MosparoGuard.php stub |
| GUARD-02 | Implement `perform()` method for validation | Complete pattern documented with Mosparo API client usage |
| GUARD-03 | Extract and validate Mosparo submission tokens | Client auto-extracts from _mosparo_submitToken and _mosparo_validationToken |
| GUARD-04 | Server-side verification via Mosparo PHP API client | Use Client::verifySubmission() with proper error handling |
| GUARD-05 | Handle API errors gracefully with user-friendly messages | Exception handling pattern with translated error keys |
| GUARD-06 | Support bypass protection (verify required fields present) | Check getVerifiedFields() for FIELD_VALID status |
| GUARD-07 | Handle ignored fields correctly (checkboxes, hidden fields) | Filter ignored fields from formData before verification |
</phase_requirements>
