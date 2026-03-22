---
phase: 03-frontend-integration
plan: 01
subsystem: frontend
tags: [mosparo, helpers, trait, template, form, widget]

requires:
  - phase: 02-core-guard
    provides: ConfigFactory, Config class with isConfigured() method

provides:
  - mosparo_field() global helper function for rendering Mosparo widget div
  - mosparo_script() global helper function for rendering Mosparo script tag
  - FormExtensions trait with mosparoField() and mosparoScript() methods
  - Automatic helper loading via MosparoPlugin::register() and index.php
  - dataAttributes plugin option for widget customization

affects:
  - 03-frontend-integration
  - template-usage
  - form-rendering

tech-stack:
  added: []
  patterns:
    - "Kirby Uniform helper pattern: function_exists() guard with PHPDoc"
    - "Trait delegation pattern: Form methods call global helpers"
    - "Silent fail pattern: Return empty string when not configured"
    - "Kirby option integration: ConfigFactory::fromKirbyOptions()"

key-files:
  created:
    - src/helpers.php - Global mosparo_field() and mosparo_script() functions
    - src/Form/FormExtensions.php - Trait with form extension methods
  modified:
    - src/MosparoPlugin.php - Added helper loading and dataAttributes option
    - index.php - Added helper loading for standalone usage

key-decisions:
  - "Helper functions use ConfigFactory::fromKirbyOptions() for configuration instead of direct option() calls"
  - "Silent fail strategy: Return empty string when Mosparo not configured to prevent broken forms"
  - "Trait delegates to global helpers to avoid duplicating logic"
  - "HTML escaping with htmlspecialchars() for all user-configurable attributes"
  - "Async and defer enabled by default on script tag for non-blocking load"

patterns-established:
  - "Template helpers: Use function_exists() check to avoid redeclaration"
  - "Configuration access: Always use ConfigFactory for type-safe option retrieval"
  - "Form extensions: Create trait that delegates to global helpers"
  - "Silent fail: Return empty string for optional features not configured"

requirements-completed:
  - FRONT-01
  - FRONT-02

duration: 2min
completed: 2026-03-06
---

# Phase 3 Plan 1: Template Helpers Summary

**Template helper functions and Form trait for Mosparo widget integration - enables developers to easily add Mosparo spam protection to forms using Kirby Uniform's established patterns.**

## Performance

- **Duration:** 2 min
- **Started:** 2026-03-06T13:28:19Z
- **Completed:** 2026-03-06T13:30:19Z
- **Tasks:** 3
- **Files modified:** 4 (2 created, 2 modified)

## Accomplishments

- Created `mosparo_field()` and `mosparo_script()` global helper functions following Kirby Uniform patterns
- Implemented `FormExtensions` trait for convenient `$form->mosparoField()` and `$form->mosparoScript()` access
- Added automatic helper loading via `MosparoPlugin::register()` and `index.php`
- Configured widget with proper data attributes (UUID, public key) from Kirby options
- Implemented silent fail strategy for production safety when Mosparo is not configured
- Added `dataAttributes` plugin option for widget customization

## Task Commits

Each task was committed atomically:

1. **Task 1: Create helper functions file** - `63f39d6` (feat)
2. **Task 2: Create Form extensions trait** - `7587f88` (feat)
3. **Task 3: Update plugin registration** - `ebcbe78` (feat)

**Plan metadata:** (to be added after final commit)

## Files Created/Modified

- `src/helpers.php` - Global helper functions `mosparo_field()` and `mosparo_script()` with PHPDoc, HTML escaping, and ConfigFactory integration
- `src/Form/FormExtensions.php` - PHP trait providing `mosparoField()` and `mosparoScript()` methods that delegate to global helpers
- `src/MosparoPlugin.php` - Added `require_once __DIR__ . '/helpers.php'` in `register()` method; added `dataAttributes` to default options
- `index.php` - Added `require_once __DIR__ . '/src/helpers.php'` for standalone plugin loading

## Decisions Made

- **Used ConfigFactory for configuration**: Rather than calling `App::instance()->option()` directly in helpers, delegated to `ConfigFactory::fromKirbyOptions()` for consistent type-safe configuration access
- **Silent fail strategy**: When Mosparo is not configured (missing host, UUID, keys), helpers return empty string rather than throw exceptions. This prevents broken forms in production if Mosparo is temporarily disabled.
- **Trait delegates to helpers**: The `FormExtensions` trait calls global helper functions rather than duplicating logic, ensuring single source of truth for widget rendering.
- **Default async/defer on scripts**: Mosparo frontend script loads with async and defer by default to avoid blocking page render, following modern best practices.

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- Template helpers complete, ready for snippet implementation (03-02)
- Helpers can be used in templates immediately via `mosparo_field()` or `$form->mosparoField()`
- Configuration system from Phase 2 provides all required settings for widget rendering

## Usage Examples

### Global Helper Functions

```php
// In a Kirby template
echo mosparo_field(['id' => 'contact-mosparo', 'class' => 'my-widget']);
echo mosparo_script();
```

### Form Trait

```php
use Uniform\Mosparo\Form\FormExtensions;

class ContactForm extends Form {
    use FormExtensions;
}

$form = new ContactForm();
echo $form->mosparoField(['id' => 'contact-mosparo']);
echo $form->mosparoScript();
```

### With Custom Data Attributes

```php
echo mosparo_field([
    'id' => 'widget',
    'data-mosparo-theme' => 'light',
    'data-mosparo-language' => 'de'
]);
```

## Self-Check: PASSED

- [x] src/helpers.php exists (146 lines)
- [x] src/Form/FormExtensions.php exists (74 lines)
- [x] src/MosparoPlugin.php modified (helper loading + dataAttributes option)
- [x] index.php modified (helper loading)
- [x] All PHP files pass syntax validation
- [x] Commit 63f39d6: Task 1 exists
- [x] Commit 7587f88: Task 2 exists
- [x] Commit ebcbe78: Task 3 exists
- [x] Helper functions return empty string when Mosparo not configured
- [x] FormExtensions trait methods exist and return strings

---
*Phase: 03-frontend-integration*
*Completed: 2026-03-06*
