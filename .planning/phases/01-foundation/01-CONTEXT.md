# Phase 1: Foundation - Context

**Gathered:** 2025-03-06
**Status:** Ready for planning

<domain>
## Phase Boundary

Plugin scaffolding with Composer, PSR-4 autoloading, and PHPUnit testing. Sets up the project structure, dependency management, and testing infrastructure required for all subsequent phases.

</domain>

<decisions>
## Implementation Decisions

### Directory Structure
- **src/** for all PHP classes, **tests/** for PHPUnit tests - clean separation
- **config/** directory for all configuration files (PHPUnit, CS-Fixer, etc.) - keeps root clean
- Object-oriented only - no public helper functions, everything accessed through classes
- Code quality tools included from day 1: PHP-CS-Fixer + PHPStan + PHPUnit

### Composer Configuration
- Package name format: `vendor/plugin-name` (standard Packagist format)
- Kirby compatibility: Kirby 4.x+ only (PHP 8.0+) - modern baseline, excludes older versions
- Package type: `kirby-plugin` for Kirby's official plugin installer support
- composer.lock: **ignored** via .gitignore (libraries shouldn't commit lock files)

### Testing Strategy
- Test structure: **tests/** mirrors **src/** structure for easy test discovery
- Mocking approach: Use Mockery or PHPUnit mocks for Kirby classes - isolated unit tests, no full Kirby installation needed
- Coverage target: **No strict target** - add tests as needed, focus on quality over metrics
- Test command: `composer test` - simple wrapper script in composer.json

### Kirby Integration Pattern
- Registration approach: **index.php** - traditional Kirby plugin entry point
- Extension points: **Guards only** for Phase 1 - MosparoGuard class for form validation
- Loading mechanism: Auto-load via Composer autoloader - Kirby recognizes composer.json and auto-registers
- Template helpers: **Include basic helpers** in Phase 1 - provide essential template functions alongside backend infrastructure

### OpenCode's Discretion
- Exact PSR-4 namespace naming (will follow Kirby conventions)
- Specific PHPUnit configuration details (xml vs php format)
- PHP-CS-Fixer rule set selection (will use modern PHP standard)
- Static analysis level for PHPStan (will use sensible baseline)

</decisions>

<specifics>
## Specific Ideas

- Project structure should feel like standard PHP library while being Kirby-native
- Follow patterns established by kirby-uniform/core for plugin organization
- Testing approach: Mock Kirby classes rather than bootstrap full Kirby instance
- Keep Phase 1 focused on infrastructure - Guards are the primary deliverable

</specifics>

<deferred>
## Deferred Ideas

- **Uniform Actions** - Add in Phase 3 (Frontend Integration) alongside template helpers
- **Advanced template helpers** - Full widget integration deferred to Phase 3
- **CI/CD setup** - Consider GitHub Actions for automated testing in Phase 4
- **Multi-language support infrastructure** - Defer i18n setup to Phase 3

</deferred>

---

*Phase: 01-foundation*
*Context gathered: 2025-03-06*
