---
phase: 01-foundation
plan: 03
subsystem: plugin

tags: [kirby, uniform, guard, psr-4, plugin-registration]

requires:
  - phase: 01-foundation
    provides: "Composer scaffold with PSR-4 autoloading (Plan 01-01)"

provides:
  - "Kirby plugin entry point via index.php"
  - "MosparoPlugin::register() for plugin extension registration"
  - "MosparoGuard stub extending Uniform Guard base class"
  - "PSR-4 autoloaded plugin structure"

affects:
  - "Phase 2: Core Guard (MosparoGuard implementation)"
  - "Phase 3: Frontend Integration (translations, snippets)"

tech-stack:
  added: []
  patterns:
    - "Kirby::plugin() registration pattern"
    - "PSR-4 autoloading: Uniform\Mosparo\ -> src/"
    - "Guard extension: extends Uniform\Guards\Guard"
    - "Defensive coding: class_exists checks"

key-files:
  created:
    - index.php
    - src/Guards/MosparoGuard.php
  modified:
    - src/MosparoPlugin.php

key-decisions:
  - "Plugin name: getkirby-uniform/mosparo (matches composer package)"
  - "Guard method: perform() must be public (matches parent class)"
  - "Stub approach: throw RuntimeException with helpful message for Phase 2"

requirements-completed:
  - INFRA-02
  - INFRA-03

duration: 4min
completed: 2026-03-06
---

# Phase 01 Plan 03: Plugin Registration and Guard Stub

**Kirby plugin registration with PSR-4 autoloading, MosparoPlugin class providing guard/options/translations/snippets registration, and MosparoGuard stub extending Uniform Guard base class**

## Performance

- **Duration:** 4 min
- **Started:** 2026-03-06T09:05:18Z
- **Completed:** 2026-03-06T09:10:13Z
- **Tasks:** 3
- **Files modified:** 3

## Accomplishments

- Created index.php as Kirby plugin entry point with Composer autoloader and defensive class_exists check
- Implemented MosparoPlugin::register() returning extension array with guards, options, translations, and snippets
- Created MosparoGuard stub class extending Uniform\Guards\Guard with public perform() method
- Established PSR-4 autoloading structure for all plugin classes
- Added defensive dependency check for kirby-uniform package

## Task Commits

Each task was committed atomically:

1. **Task 1: Create index.php Kirby plugin entry point** - `da0dbe1` (feat)
2. **Task 2: Implement MosparoPlugin class with registration logic** - `6a7f00d` (feat)
3. **Task 3: Create MosparoGuard stub extending Uniform Guard** - `ddec552` (feat)

**Plan metadata:** [to be committed]

## Files Created/Modified

- `index.php` - Kirby plugin entry point, loads autoloader, registers plugin via Kirby::plugin()
- `src/MosparoPlugin.php` - Main plugin class with register() method returning guards/options/translations/snippets
- `src/Guards/MosparoGuard.php` - Guard stub extending Uniform Guard, implements perform() method

## Decisions Made

- **Plugin naming:** Used `getkirby-uniform/mosparo` matching the Composer package name for consistency
- **Guard visibility:** Changed perform() from protected to public after discovering parent class requires public visibility
- **Stub approach:** Implemented as stub throwing RuntimeException with helpful message indicating Phase 2 implementation
- **Defensive checks:** Added class_exists() checks for both Kirby context and kirby-uniform dependency

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Fixed MosparoGuard::perform() visibility**
- **Found during:** Task 3 (MosparoGuard stub implementation)
- **Issue:** Plan specified `protected function perform()` but parent class `Uniform\Guards\Guard` requires public visibility
- **Fix:** Changed method visibility from protected to public
- **Files modified:** src/Guards/MosparoGuard.php
- **Verification:** PHP reflection confirms public visibility; class loads without errors
- **Committed in:** ddec552 (Task 3 commit)

---

**Total deviations:** 1 auto-fixed (1 bug)
**Impact on plan:** Minor - visibility correction required by PHP inheritance rules. No scope creep.

## Issues Encountered

- Initial test of MosparoGuard instantiation failed due to required constructor arguments (expected - Guard needs Form instance)
- Adjusted verification to use class_exists() and reflection instead of instantiation

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- ✓ Plugin structure complete and auto-loading correctly
- ✓ Guard stub ready for Phase 2 implementation
- ✓ Extension points prepared for translations (Phase 3) and snippets (Phase 3)
- Ready for Phase 2: Core Guard implementation with actual Mosparo API integration

---
*Phase: 01-foundation*
*Completed: 2026-03-06*
