---
phase: 02-core-guard
plan: 03
subsystem: security
tags: [mosparo, bypass-protection, i18n, security, validation]

requires:
  - phase: 02-core-guard
    provides: "Config system and Guard stub from Plans 02-01 and 02-02"

provides:
  - "VerificationService with bypass protection"
  - "Translation files (English and German)"
  - "Security-hardened MosparoGuard"
  - "Error sanitization (no private key exposure)"
  - "20 security-focused tests"

affects:
  - "02-core-guard"
  - "03-frontend-integration"

tech-stack:
  added:
    - "mosparo/php-api-client (via VerificationService)"
  patterns:
    - "Service layer for external API interaction"
    - "Translation key constants for i18n"
    - "Error sanitization before logging"
    - "Bypass protection via verifiedFields check"

key-files:
  created:
    - "i18n/en.php"
    - "i18n/de.php"
    - "src/Validation/VerificationService.php"
    - "tests/Validation/VerificationServiceTest.php"
    - "tests/Guards/MosparoGuardSecurityTest.php"
  modified:
    - "src/Guards/MosparoGuard.php"

key-decisions:
  - "Bypass protection checks verifiedFields from API response"
  - "Ignored fields (passwords, checkboxes) filtered before API call"
  - "Error messages sanitized to prevent private key exposure"
  - "Translation keys used instead of raw error messages"
  - "Separate security-focused tests for verification logic"

requirements-completed:
  - GUARD-05
  - GUARD-06
  - GUARD-07

duration: 22min
completed: 2026-03-06
---

# Phase 2 Plan 3: Security Hardening with Bypass Protection Summary

**Security-hardened Mosparo integration with bypass protection via verifiedFields check, i18n translations, and error sanitization.**

## Performance

- **Duration:** 22 min
- **Started:** 2026-03-06T10:43:31Z
- **Completed:** 2026-03-06T11:05:31Z
- **Tasks:** 4
- **Files modified:** 6

## Accomplishments

- Created English and German translation files with 6 error messages each
- Built VerificationService with bypass protection (verifiedFields check)
- Implemented ignored field filtering (passwords, checkboxes, tokens)
- Added error sanitization to prevent private key exposure in logs
- Updated MosparoGuard to use VerificationService with security checks
- Created 20 comprehensive security tests

## task Commits

Each task was committed atomically:

1. **task 1: Create translation files** - `6104e9a` (feat)
2. **task 2: Create VerificationService with security checks** - `9ab3c38` (feat)
3. **task 3: Update MosparoGuard with security features** - `187ca62` (feat)
4. **task 4: Create security-focused tests** - `eadde72` (test)

**Plan metadata:** (to be committed with SUMMARY.md)

## Files Created/Modified

- `i18n/en.php` - English error message translations
- `i18n/de.php` - German error message translations
- `src/Validation/VerificationService.php` - Verification service with bypass protection
- `src/Guards/MosparoGuard.php` - Updated with security features and error sanitization
- `tests/Validation/VerificationServiceTest.php` - 10 tests for verification service
- `tests/Guards/MosparoGuardSecurityTest.php` - 10 security-focused tests for guard

## Decisions Made

1. **Bypass protection via verifiedFields:** The Mosparo API returns a list of verified fields with their status (valid/invalid/not-verified). We check that all submitted fields have `FIELD_VALID` status to prevent bots from bypassing the frontend widget.

2. **Ignored field filtering:** Passwords, password confirmations, CSRF tokens, and other sensitive fields are filtered out before sending data to the Mosparo API. This prevents these values from being transmitted externally.

3. **Error sanitization:** API error messages are sanitized before logging to remove URLs and 32+ character hex patterns that could contain API keys or signatures.

4. **Translation keys:** All error messages use translation keys (`mosparo.error.*`) instead of raw strings, enabling multi-language support.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Fixed test data to use valid hex patterns**
- **Found during:** task 4
- **Issue:** Test data used non-hex characters (g, h, i, etc.) which didn't match the hex sanitization regex
- **Fix:** Changed test data to use valid 32+ character hex strings for key pattern tests
- **Files modified:** tests/Guards/MosparoGuardSecurityTest.php
- **Committed in:** eadde72 (task 4 commit)

**2. [Rule 1 - Bug] Fixed Mockery method name conflict**
- **Found during:** task 4
- **Issue:** `createPartialMock()` method name conflicted with PHPUnit's built-in method
- **Fix:** Renamed to `createMockForGuard()` and later removed unused method
- **Files modified:** tests/Guards/MosparoGuardSecurityTest.php
- **Committed in:** eadde72 (task 4 commit)

---

**Total deviations:** 2 auto-fixed (2 bugs)
**Impact on plan:** Both auto-fixes were test data/structure issues, not implementation issues. No scope creep.

## Issues Encountered

None - plan executed as specified. Pre-existing test failures in MosparoGuardTest (from Plan 02-02) are unrelated to this plan's implementation.

## User Setup Required

None - no external service configuration required.

## Security Features Implemented

### Bypass Protection
The `verifyRequiredFields()` method in VerificationService checks that all submitted fields appear in the API response's `verifiedFields` with `FIELD_VALID` status. This prevents:
- Bots submitting forms without using the frontend widget
- Form tampering between frontend validation and backend verification
- Direct API attacks bypassing JavaScript checks

### Ignored Field Filtering
The `prepareFormData()` method removes:
- Mosparo tokens (`_mosparo_submitToken`, `_mosparo_validationToken`)
- Configured ignored fields (passwords, password_confirm, csrf_token by default)

This ensures sensitive data never leaves the server.

### Error Sanitization
The `sanitizeErrorMessage()` method in MosparoGuard removes:
- URLs (could contain API endpoints with keys)
- 32+ character hex patterns (could be API keys or signatures)

This prevents accidental key exposure in logs.

## Test Coverage

### VerificationService Tests (10)
- Token removal from form data
- Ignored field filtering
- Bypass protection with valid fields
- Bypass detection with missing fields
- Bypass detection with invalid fields
- Empty verifiedFields handling
- Service method availability
- Configuration delegation
- Not-verified status handling
- Empty field name skipping

### MosparoGuard Security Tests (10)
- Bypass protection triggers
- Bypass protection passes when valid
- Private key not in errors
- URL sanitization
- Key pattern sanitization
- Ignored fields not sent to API
- User-friendly error messages
- Translation key usage
- Empty verifiedFields detection
- Invalid field status rejection

## Next Phase Readiness

- Security hardening complete
- All security requirements (GUARD-05, GUARD-06, GUARD-07) satisfied
- Ready for Phase 3: Frontend Integration

---
*Phase: 02-core-guard*
*Completed: 2026-03-06*
