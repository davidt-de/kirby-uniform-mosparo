# Research Synthesis: Kirby Uniform Mosparo Plugin

**Project:** Kirby Uniform Mosparo Plugin  
**Domain:** Kirby CMS CAPTCHA/Spam Protection Plugin  
**Synthesized:** March 6, 2026  
**Overall Confidence:** HIGH

---

## Executive Summary

The Kirby Uniform Mosparo Plugin is a CAPTCHA-style spam protection plugin that integrates the self-hosted Mosparo service with Kirby CMS's Uniform form framework. Unlike competitors (Google reCAPTCHA, hCaptcha, Cloudflare Turnstile) that send user data to third-party clouds, Mosparo offers complete data sovereignty—a significant GDPR advantage for European users and privacy-conscious organizations.

Based on comprehensive research of existing Uniform CAPTCHA implementations (reCAPTCHA, hCaptcha, Turnstile), the recommended approach follows a well-established three-layer architecture: frontend template helpers for widget rendering, a Guard class extending Uniform's validation framework, and server-side verification via the official Mosparo PHP API client. The stack targets PHP 8.2+ to support Kirby 3.5 through Kirby 5, with the Uniform 5.x plugin providing the foundational Guard API.

Key risks center on Mosparo's complex 11-step verification process—field name mismatches, improper handling of ignored field types (checkboxes, passwords, hidden fields), and missing server-side verification are common failure modes. The plugin must also guard against bypass attacks by verifying that all required fields appear in Mosparo's verifiedFields response. These security requirements, combined with Uniform's specific integration patterns, suggest a phased development approach that prioritizes core verification logic before adding convenience features.

---

## Key Findings

### From Stack Research (STACK.md)

**Core Technology Stack:**

| Technology | Version | Rationale |
|------------|---------|-----------|
| PHP | 8.2+ (8.3 recommended) | Kirby 5 requires 8.2+; 8.3 offers best performance/stability balance |
| Kirby CMS | ^3.5 \| ^4.0 \| ^5.0 | Uniform 5.x officially supports these versions |
| Uniform Plugin | ^5.0 | De facto standard for Kirby forms; provides Guard API architecture |
| Mosparo PHP API Client | ^1.1 | Official Mosparo library handles HMAC-SHA256 signatures; prevents crypto reimplementation errors |

**Critical Dependencies:**
- Mosparo frontend JS/CSS loaded directly from user's Mosparo instance (`/build/mosparo-frontend.js`)
- Composer autoloading with PSR-4 structure
- Server-side only private key storage (never expose in frontend)

**What NOT to Use:**
- Manual verification implementation (error-prone HMAC logic)
- Kirby 3.0-3.4 (Uniform 5.x requires 3.5+)
- Custom JavaScript widgets (bypasses Mosparo security updates)
- PHP 7.4 (end-of-life, no security updates)

### From Feature Research (FEATURES.md)

**Table Stakes (Must Have for v1.0):**

| Feature | Priority | Notes |
|---------|----------|-------|
| Magic Method Guard (`mosparoGuard()`) | P1 | Essential Uniform integration pattern |
| Template Helper: Field (`mosparoField()`) | P1 | Renders Mosparo widget with custom CSS class support |
| Template Helper: Script (`mosparoScript()`) | P1 | Loads Mosparo JS with async/defer attributes |
| Configuration: Host URL, Site Key, Secret Key | P1 | Mosparo is self-hosted—requires configurable host |
| Composer Installation | P1 | Standard PHP distribution |
| i18n Support | P1 | Multi-language error messages via Kirby's i18n system |
| Kirby 3 & 4 Compatibility | P1 | All competitor plugins target these versions |

**Differentiators (Competitive Advantage):**

| Feature | Value | Release |
|---------|-------|---------|
| Self-Hosted Privacy | GDPR advantage vs cloud CAPTCHAs | v1.0 (marketing) |
| Theme Customization | Match widget to site design | v1.1+ |
| Debug Mode | Verbose logging for troubleshooting | v1.1+ |
| Field Mapping | Map ignored fields per form | v1.1+ |
| Custom Verification Rules | Mosparo rule system exposure | v1.1+ |
| Multiple Instance Support | Different Mosparo projects per form | v1.2+ |

**Anti-Features (Intentionally Excluded):**
- Fallback to reCAPTCHA (violates privacy-first approach)
- Score-based validation (Mosparo uses pass/fail by design)
- Invisible mode without visible fallback (accessibility concern)
- Admin dashboard widget (Mosparo has its own dashboard)
- Automatic form detection (causes conflicts, prefer explicit opt-in)

### From Architecture Research (ARCHITECTURE.md)

**System Architecture (Three Layers):**

```
┌─────────────────────────────────────────────────────────────┐
│  Kirby Plugin Layer                                          │
│  ├── index.php (registration, options, autoloading)         │
│  ├── helpers.php (mosparoField(), mosparoScript())          │
│  └── i18n/*.php (translations)                              │
├─────────────────────────────────────────────────────────────┤
│  Uniform Integration Layer                                   │
│  └── MosparoGuard extends Uniform\Guards\Guard              │
│      ├── perform(): Validates via API                       │
│      └── reject(): Throws PerformerException                │
├─────────────────────────────────────────────────────────────┤
│  External Service Layer                                      │
│  ├── mosparo PHP Client (Composer package)                  │
│  └── Mosparo Instance (self-hosted)                         │
└─────────────────────────────────────────────────────────────┘
```

**Guard Implementation Pattern:**
- Extend `Uniform\Guards\Guard`
- Override `perform()` method
- Extract `_mosparo_submitToken` and `_mosparo_validationToken` from request
- Verify submission via Mosparo PHP Client
- Call `$this->reject()` on failure
- Clean up Mosparo fields with `$this->form->forget()`

**Data Flow:**
1. Frontend: Mosparo JS validates client-side and injects tokens
2. Submit: Form POSTs with tokens
3. Controller: `$form->mosparoGuard()` triggers validation
4. Guard: Server-side verification via Mosparo API
5. Result: Success continues to actions, failure redirects with error

**Recommended Project Structure:**
```
kirby-uniform-mosparo/
├── index.php              # Plugin registration
├── composer.json          # Dependencies
├── src/
│   ├── Guards/
│   │   └── MosparoGuard.php
│   └── helpers.php        # Template functions
└── i18n/
    ├── en.php
    └── de.php
```

### From Pitfalls Research (PITFALLS.md)

**Top 5 Critical Pitfalls:**

| Rank | Pitfall | Impact | Prevention |
|------|---------|--------|------------|
| 1 | **Frontend-Only Validation** | Complete bypass by bots | Always verify server-side with PHP client |
| 2 | **Field Name Mismatch** | All submissions rejected | Use exact HTML `name` attributes; preserve array structure |
| 3 | **Ignored Fields Not Filtered** | Signature mismatches | Strip checkboxes, radio, password, hidden fields before verification |
| 4 | **Missing Bypass Protection** | Spam with empty required fields | Verify all required fields exist in `verifiedFields` response |
| 5 | **Uniform Integration Anti-Patterns** | Guard runs at wrong time | Follow Uniform's guard interface; implement `__invoke()` correctly |

**Security Requirements:**
- Keep private key server-side only (never in JS)
- Never trust frontend validation
- Don't log `_mosparo_*` tokens (single-use)
- Always use HTTPS for Mosparo host
- Implement CSP headers per Mosparo docs

**UX Requirements:**
- Show loading state during validation
- Display specific Mosparo error messages
- Support visible mode fallback for no-JS scenarios
- Include accessibility labels for screen readers
- Fail open (allow submission) on API errors with logging

---

## Implications for Roadmap

### Suggested Phase Structure

Based on research findings, recommend **4 phases**:

#### Phase 1: Foundation (v0.1)
**Rationale:** Core infrastructure must be in place before any functional code. Establishes build pipeline, testing framework, and plugin structure.

**Delivers:**
- Project scaffolding with Composer
- PSR-4 autoloading configuration
- PHPUnit testing framework
- Basic plugin registration (`index.php`)
- Development environment setup

**Includes Features:**
- Composer package definition
- Plugin folder structure
- Basic CI/CD pipeline

**Avoids Pitfalls:**
- None (foundation only)

**Research Flag:** ⚠️ SKIP - Standard patterns, no research needed

---

#### Phase 2: Core Guard Implementation (v0.2 → v0.9)
**Rationale:** The Guard is the heart of the plugin. Must implement Mosparo's 11-step verification correctly before any other features. High complexity, high security requirements.

**Delivers:**
- `MosparoGuard` class extending `Uniform\Guards\Guard`
- Integration with `mosparo/php-api-client`
- Server-side token verification
- Field filtering (ignored field types)
- Bypass protection checks
- Configuration validation

**Includes Features:**
- Magic Method Guard (`mosparoGuard()`)
- Configuration: Host URL, Site Key, Secret Key
- Basic error handling

**Avoids Pitfalls:**
- Frontend-only validation (P1)
- Field name mismatch (P2)
- Ignored fields not filtered (P3)
- Missing bypass protection (P4)

**Research Flag:** 🔍 RESEARCH RECOMMENDED - Complex 11-step verification process, needs validation testing

---

#### Phase 3: Frontend Integration (v1.0-alpha → v1.0-rc)
**Rationale:** Once backend verification works, add the frontend components that users interact with. Depends on Phase 2 for verification logic.

**Delivers:**
- Template helper functions (`mosparoField()`, `mosparoScript()`)
- Frontend JavaScript integration
- CSRF token handling
- Basic i18n (English + German)
- Documentation and examples
- Uniform integration tests

**Includes Features:**
- Template Helper: Field
- Template Helper: Script
- i18n Support (basic)
- Kirby 3 & 4 Compatibility verification

**Avoids Pitfalls:**
- Uniform integration anti-patterns (P5)
- No error handling
- Missing translations (partial)

**Research Flag:** ⚠️ SKIP - Follows established helper patterns from reCAPTCHA/hCaptcha plugins

---

#### Phase 4: Polish & Extensions (v1.0 → v1.x)
**Rationale:** After core functionality is stable, add convenience features and optimizations.

**Delivers:**
- Theme customization options
- Debug mode
- Extended i18n (additional languages)
- Field mapping for ignored fields
- Custom verification rules exposure
- Performance optimizations (conditional JS loading)
- Accessibility improvements

**Includes Features:**
- Theme Customization (P2)
- Debug Mode (P2)
- Field Mapping (P2)
- Custom Verification Rules (P2)
- Multiple Instance Support (P2)

**Avoids Pitfalls:**
- Performance traps (sync API calls, no timeout)
- UX pitfalls (no loading state, wrong language)
- CSP issues

**Research Flag:** ⚠️ SKIP - Incremental feature additions based on user feedback

---

### Phase Dependencies Graph

```
Phase 1: Foundation
    ↓ (provides build/test infrastructure)
Phase 2: Core Guard
    ↓ (provides verification logic)
Phase 3: Frontend Integration
    ↓ (provides complete plugin)
Phase 4: Polish & Extensions
```

**Key Dependency:** Phase 3 CANNOT start until Phase 2 verification is working correctly—frontend needs backend to verify tokens.

---

## Confidence Assessment

| Area | Confidence | Rationale |
|------|------------|-----------|
| **Stack** | HIGH | Official Kirby docs, Uniform composer.json verified, Mosparo PHP client v1.1.0 stable since Jan 2024 |
| **Features** | HIGH | Three reference implementations (reCAPTCHA, hCaptcha, Turnstile) with identical feature sets; clear patterns |
| **Architecture** | HIGH | Uniform Guard API is well-documented; reference implementations show exact patterns |
| **Pitfalls** | MEDIUM-HIGH | Official Mosparo docs detail 11-step verification; some edge cases may emerge during implementation |
| **Overall** | HIGH | Well-understood domain with established patterns; clear reference implementations |

---

## Gaps to Address

### During Planning

1. **Mosparo Instance Testing**
   - Need access to a live Mosparo instance for integration testing
   - Recommendation: Set up test Mosparo instance during Phase 2 planning

2. **Kirby 5 Compatibility Testing**
   - Uniform 5.x includes Kirby 5.0.0-rc.1 in dev dependencies
   - No breaking changes expected, but formal testing needed

3. **Field Name Edge Cases**
   - Complex nested field names (`form[address][street]`) need testing
   - Array notation handling should be verified with real Mosparo responses

4. **Translation Coverage**
   - Research identified German as priority (Mosparo is EU-based)
   - Additional languages (French, Spanish) should be community-contributed post-v1.0

### During Development

1. **CSP Compatibility**
   - Mosparo requires specific CSP headers
   - Should test with strict CSP configurations

2. **Performance Under Load**
   - No caching strategy defined yet
   - High-traffic sites may need submission result caching (deferred to v2+)

3. **Accessibility Audit**
   - Screen reader compatibility needs verification
   - Keyboard navigation flows should be tested

---

## Roadmap Implications Summary

### Quick Start Recommendation

**For v1.0 MVP:**
1. **Phase 1:** Scaffold project with Composer (1-2 days)
2. **Phase 2:** Build core Guard with verification logic (3-5 days) — RESEARCH THIS PHASE
3. **Phase 3:** Add template helpers and basic i18n (2-3 days)
4. **Phase 4:** Theme support and debug mode (1-2 days)

**Total estimated time to v1.0:** 1-2 weeks of focused development

### Risk Mitigation

| Risk | Mitigation Strategy |
|------|---------------------|
| Mosparo verification complexity | Use official PHP client; research phase for 11-step verification |
| Field name mismatches | Extensive testing with complex form structures |
| Security vulnerabilities | Code review against pitfalls checklist; never skip server-side verification |
| Uniform integration issues | Follow reference implementations exactly; test full form lifecycle |

### Success Criteria

The roadmap should deliver:
- ✅ Drop-in replacement for existing CAPTCHA plugins (reCAPTCHA, hCaptcha, Turnstile)
- ✅ Privacy-first positioning (GDPR compliance)
- ✅ Simple installation via Composer
- ✅ Clear documentation with template examples
- ✅ Battle-tested verification logic (no frontend-only validation)

---

## Sources

- [Kirby Uniform Repository](https://github.com/mzur/kirby-uniform) — Plugin structure and Guard API
- [Kirby Uniform reCAPTCHA](https://github.com/eXpl0it3r/kirby-uniform-recaptcha) — Reference implementation (Kirby 3/4)
- [Kirby Uniform hCaptcha](https://github.com/lukasleitsch/kirby-uniform-hcaptcha) — Reference implementation (Kirby 3)
- [Kirby Uniform Turnstile](https://github.com/anselmh/kirby-uniform-turnstile) — Reference implementation (Kirby 3/4)
- [Mosparo Custom Integration Docs](https://documentation.mosparo.io/docs/integration/custom) — 11-step verification process
- [Mosparo PHP API Client](https://github.com/mosparo/php-api-client) — Official server-side verification library
- [Mosparo Plugin Integration Docs](https://documentation.mosparo.io/docs/integration/with_plugins) — Official plugin patterns
- [Kirby Requirements](https://getkirby.com/docs/guide/quickstart) — PHP version requirements
- [Kirby Plugin Best Practices](https://getkirby.com/docs/guide/plugins/best-practices) — Plugin conventions
- [Kirby Uniform GitHub Issues](https://github.com/mzur/kirby-uniform/issues) — Real-world guard problems

---

*Research synthesis for: Kirby Uniform Mosparo Plugin*  
*Synthesized: March 6, 2026*
