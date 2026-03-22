# Requirements: Kirby Uniform Mosparo Plugin

**Defined:** 2025-03-06
**Core Value:** Users can protect their Kirby Uniform forms from spam using Mosparo's privacy-friendly, GDPR-compliant protection

## v1 Requirements

### Plugin Infrastructure

- [x] **INFRA-01**: Composer package with PSR-4 autoloading
- [x] **INFRA-02**: Kirby plugin registration via `index.php`
- [x] **INFRA-03**: Support Kirby 3.5+, 4.x, and 5.x
- [x] **INFRA-04**: PHPUnit test setup with mocked Mosparo API

### Configuration

- [x] **CONFIG-01**: Mosparo host URL configurable via Kirby options
- [x] **CONFIG-02**: Project UUID and public key for frontend
- [x] **CONFIG-03**: Private key for server-side (secure, not exposed to frontend)
- [x] **CONFIG-04**: Validation mode selection (checkbox/invisible)
- [x] **CONFIG-05**: Optional: Custom CSS for widget styling

### Core Guard Implementation

- [ ] **GUARD-01**: Extend `Uniform\Guards\Guard` base class
- [ ] **GUARD-02**: Implement `perform()` method for validation
- [ ] **GUARD-03**: Extract and validate Mosparo submission tokens
- [ ] **GUARD-04**: Server-side verification via Mosparo PHP API client
- [ ] **GUARD-05**: Handle API errors gracefully with user-friendly messages
- [ ] **GUARD-06**: Support bypass protection (verify required fields present)
- [ ] **GUARD-07**: Handle ignored fields correctly (checkboxes, hidden fields)

### Frontend Integration

- [x] **FRONT-01**: `mosparoField()` helper for template form integration
- [x] **FRONT-02**: `mosparoScript()` helper to load Mosparo JS/CSS
- [ ] **FRONT-03**: Automatic initialization of Mosparo widget
- [ ] **FRONT-04**: Support for data attributes (data-mosparo-*)

### Internationalization

- [ ] **I18N-01**: German translations (de)
- [ ] **I18N-02**: English translations (en)
- [ ] **I18N-03**: Error message keys for all validation failures

### Documentation

- [x] **DOCS-01**: README with installation instructions (Composer)
- [ ] **DOCS-02**: Usage examples for templates
- [ ] **DOCS-03**: Configuration reference
- [x] **DOCS-04**: Migration guide from other CAPTCHA plugins

## v2 Requirements

### Advanced Features

- **ADV-01**: Theme customization support (colors, sizing)
- **ADV-02**: Debug mode with detailed logging
- **ADV-03**: Multi-site support (different Mosparo instances per site)
- **ADV-04**: Custom verification rules integration
- **ADV-05**: Field mapping configuration

### Developer Experience

- **DEV-01**: Kirby Panel field preview
- **DEV-02**: Health check endpoint
- **DEV-03**: Statistics dashboard integration

## Out of Scope

| Feature | Reason |
|---------|--------|
| Fallback to reCAPTCHA/hCaptcha | Would add external dependencies, contradicts privacy focus |
| Score-based validation | Mosparo doesn't use scoring like reCAPTCHA v3 |
| Automatic form detection | Too complex, manual integration is clearer |
| Admin dashboard widget | Out of scope for initial release |
| Built-in Mosparo server | Self-hosting is user's responsibility |
| Mobile app support | Kirby is web-focused |

## Traceability

| Requirement | Phase | Phase Name | Status |
|-------------|-------|------------|--------|
| INFRA-01 | 1 | Foundation | Pending |
| INFRA-02 | 1 | Foundation | Pending |
| INFRA-03 | 1 | Foundation | Pending |
| INFRA-04 | 1 | Foundation | Pending |
| CONFIG-01 | 2 | Core Guard | Pending |
| CONFIG-02 | 2 | Core Guard | Pending |
| CONFIG-03 | 2 | Core Guard | Pending |
| CONFIG-04 | 2 | Core Guard | Pending |
| CONFIG-05 | 2 | Core Guard | Pending |
| GUARD-01 | 2 | Core Guard | Pending |
| GUARD-02 | 2 | Core Guard | Pending |
| GUARD-03 | 2 | Core Guard | Pending |
| GUARD-04 | 2 | Core Guard | Pending |
| GUARD-05 | 2 | Core Guard | Pending |
| GUARD-06 | 2 | Core Guard | Pending |
| GUARD-07 | 2 | Core Guard | Pending |
| FRONT-01 | 3 | Frontend Integration | Complete |
| FRONT-02 | 3 | Frontend Integration | Complete |
| FRONT-03 | 3 | Frontend Integration | Pending |
| FRONT-04 | 3 | Frontend Integration | Pending |
| I18N-01 | 3 | Frontend Integration | Pending |
| I18N-02 | 3 | Frontend Integration | Pending |
| I18N-03 | 3 | Frontend Integration | Pending |
| DOCS-01 | 4 | Documentation & Release | Pending |
| DOCS-02 | 4 | Documentation & Release | Pending |
| DOCS-03 | 4 | Documentation & Release | Pending |
| DOCS-04 | 4 | Documentation & Release | Pending |

**Coverage:**
- v1 requirements: 25 total
- Mapped to phases: 25
- Unmapped: 0 ✓

### Phase Summary

| Phase | Name | Requirements | Count |
|-------|------|--------------|-------|
| 1 | Foundation | INFRA-01..04 | 4 |
| 2 | Core Guard | CONFIG-01..05, GUARD-01..07 | 12 |
| 3 | Frontend Integration | FRONT-01..04, I18N-01..03 | 7 |
| 4 | Documentation & Release | DOCS-01..04 | 4 |
| **Total** | | | **25** |

---
*Requirements defined: 2025-03-06*
*Last updated: 2025-03-06 after research synthesis*
