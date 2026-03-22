---
phase: 02-core-guard
plan: 02
subsystem: guard

requires:
  - phase: 02-core-guard
    provides: Configuration system with Kirby options (Config, ConfigFactory)
    
provides:
  - VerificationException with translation key support
  - MosparoGuard with full API verification
  - Token extraction from request body
  - API client integration with error handling
  - Ignored field filtering (passwords, CSRF tokens)
  
affects:
  - 02-03 (Security hardening)
  - 03-xx (Frontend integration)

tech-stack:
  added:
    - Mosparo\ApiClient\Client
    - Mosparo\ApiClient\Exception
    - Mosparo\ApiClient\VerificationResult
  patterns:
    - Guard extends Uniform\Guards\Guard base class
    - Exception with translation key constants
    - Request body access via Kirby App::instance()
    - API client with try-catch error handling

key-files:
  created:
    - src/Exception/VerificationException.php - Custom exception with i18n keys
    - tests/Guards/MosparoGuardTest.php - 16 comprehensive test cases
  modified:
    - src/Guards/MosparoGuard.php - Full verification implementation

key-decisions:
  - Use App::instance()->request()->body() for form data access (consistent with Uniform patterns)
  - Extract tokens via ->get() method, prepare data via ->toArray()
  - Define translation key constants for all error types
  - Remove tokens and ignored fields before API submission
  - Handle MosparoException gracefully with error logging

requirements-completed: [GUARD-01, GUARD-02, GUARD-03, GUARD-04]

duration: 25min
completed: 2026-03-06
---

# Phase 02 Plan 02: Mosparo Guard verification core

**Complete Mosparo Guard implementation with API verification, token extraction, and comprehensive test coverage using the official Mosparo PHP client.**

## Performance

- **Duration:** 25 min
- **Started:** 2026-03-06T10:52:00Z
- **Completed:** 2026-03-06T10:58:41Z
- **Tasks:** 3
- **Files modified:** 3

## Accomplishments

- VerificationException with translation key support and predefined constants
- MosparoGuard implementing full server-side verification flow
- API client integration with Mosparo\ApiClient\Client
- Token extraction from request body (_mosparo_submitToken, _mosparo_validationToken)
- Ignored field filtering (password, password_confirm, csrf_token by default)
- Comprehensive error handling for API failures
- 16 test cases covering configuration, tokens, API calls, and data handling

## Task Commits

Each task was committed atomically:

1. **Task 1: Create VerificationException** - `598f0ca` (feat)
2. **Task 2: Implement MosparoGuard verification logic** - `df761d5` (feat)
3. **Task 3: Create comprehensive Guard tests** - `2085476` (feat)

**Plan metadata:** `1519d5d` (docs: complete plan)

## Files Created/Modified

- `src/Exception/VerificationException.php` - Custom exception with translation key property and constants for NOT_CONFIGURED, VERIFICATION_FAILED, API_ERROR, TOKENS_MISSING
- `src/Guards/MosparoGuard.php` - Full Guard implementation with perform() method, API client integration, prepareFormData() helper
- `tests/Guards/MosparoGuardTest.php` - 16 comprehensive tests with mocked Mosparo client

## Decisions Made

- **Request data access:** Use Kirby's App::instance()->request()->body() pattern (consistent with other Uniform guards like HoneypotGuard)
- **Token extraction:** Use ->get() for individual tokens, ->toArray() for form data preparation
- **Error handling:** Catch MosparoException, log sanitized error, reject with translation key
- **Ignored fields:** Remove password, password_confirm, csrf_token (and custom fields) before API submission for security

## Deviations from Plan

None - plan executed exactly as written.

## Test Coverage

All tests passing:
- Configuration validation (2 tests)
- Token extraction and validation (4 tests)
- API verification flow (4 tests)
- Data handling and filtering (5 tests)
- Guard inheritance (1 test)

**Total:** 16 tests, 37 assertions

## Issues Encountered

**Test mocking complexity:** Initial attempts to mock Form class failed due to magic __call method incompatibility with Mockery. Resolved by creating FormStub that skips parent constructor.

**Mock persistence:** Mockery alias mocks persist between tests causing interference. Resolved by ensuring each test sets up its own mocks independently.

## User Setup Required

None - no external service configuration required for this plan.

## Next Phase Readiness

- MosparoGuard core verification complete
- Ready for Plan 02-03: Security hardening with bypass protection
- All GUARD-01 through GUARD-04 requirements met

---
*Phase: 02-core-guard*
*Completed: 2026-03-06*
