# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Placeholder for future changes

## [1.0.0] - 2026-03-07

### Added
- Initial release of Kirby Uniform Mosparo plugin
- Composer installation with PSR-4 autoloading (`Uniform\Mosparo\` → `src/`)
- Kirby plugin registration via `Kirby::plugin()` with automatic extension loading
- Kirby 3.5+, 4.x, and 5.x compatibility
- Mosparo Guard for Uniform forms (`MosparoGuard::perform()`)
- Server-side verification via Mosparo PHP API client (^1.0)
- Configuration system with Kirby options (`davidt-de.uniform-mosparo.*`)
- Configuration value object with readonly properties (PHP 8.1+)
- Template helper functions (`mosparo_field()`, `mosparo_script()`)
- Form trait extensions (`$form->mosparoField()`, `$form->mosparoScript()`)
- WidgetRenderer class with data attributes support
- Security hardening with bypass protection (verifiedFields check)
- Ignored field handling (password, password_confirm, csrf_token excluded)
- API error handling with user-friendly error messages
- Debug mode for troubleshooting
- German translations (de.php)
- English translations (en.php)
- Privacy-focused GDPR-compliant spam protection (no tracking cookies)
- PHPUnit test suite with Mockery for API mocking
- 69 passing tests covering Guard, Config, Validation, and Widget functionality

### Security
- Private keys never exposed to frontend code or logs
- Automatic token extraction and validation
- Bypass detection for bot submissions
- Error sanitization to prevent information leakage

[Unreleased]: https://github.com/davidt-de/uniform-mosparo/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/davidt-de/uniform-mosparo/releases/tag/v1.0.0
