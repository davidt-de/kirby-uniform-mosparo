# Project Roadmap: Kirby Uniform Mosparo Plugin

**Created:** 2025-03-06  
**Depth:** Standard  
**Phases:** 4  
**Total v1 Requirements:** 25

---

## Phases

- [ ] **Phase 1: Foundation** - Plugin scaffolding with Composer, PSR-4 autoloading, and PHPUnit testing
- [ ] **Phase 2: Core Guard** - Mosparo Guard implementation with server-side verification and security hardening
- [ ] **Phase 3: Frontend Integration** - Template helpers, widget integration, and i18n support
- [ ] **Phase 4: Documentation & Release** - Installation docs, usage examples, and migration guides

---

## Phase Details

### Phase 1: Foundation

**Goal:** Plugin scaffolding with Composer, PSR-4 autoloading, and PHPUnit testing

**Depends on:** Nothing (first phase)

**Requirements:** INFRA-01, INFRA-02, INFRA-03, INFRA-04

**Success Criteria** (what must be TRUE):
1. Developer can install plugin via `composer require`
2. Plugin auto-registers with Kirby when placed in `site/plugins/`
3. Plugin loads without errors on Kirby 3.5+, 4.x, and 5.x
4. Developer can run `composer test` to execute PHPUnit tests

**Plans:** 3 plans

**Plan List:**
- [x] 01-01-PLAN.md — Composer package with PSR-4 autoloading and project structure (Complete: 2026-03-06)
- [ ] 01-02-PLAN.md — PHPUnit testing infrastructure with Mockery
- [ ] 01-03-PLAN.md — Kirby plugin registration and Guard stub

**Research Flag:** ⚠️ SKIP - Standard patterns, no research needed

---

### Phase 2: Core Guard

**Goal:** Mosparo Guard implementation with server-side verification and security hardening

**Depends on:** Phase 1

**Requirements:** CONFIG-01, CONFIG-02, CONFIG-03, CONFIG-04, CONFIG-05, GUARD-01, GUARD-02, GUARD-03, GUARD-04, GUARD-05, GUARD-06, GUARD-07

**Success Criteria** (what must be TRUE):
1. Admin can configure Mosparo host URL, UUID, and keys via Kirby options
2. Form submissions with valid Mosparo tokens pass validation
3. Form submissions with invalid/expired tokens are rejected with error
4. Bot submissions bypassing frontend widgets are blocked (verifiedFields check)
5. Forms with checkboxes, passwords, and hidden fields validate correctly (ignored field handling)
6. API errors show user-friendly error messages (no raw exceptions)
7. Private keys never appear in frontend code or logs

**Plans:** 3 plans

**Plan List:**
- [x] 02-01-PLAN.md — Configuration system with Kirby options (CONFIG-01..05) (Complete: 2026-03-06)
- [x] 02-02-PLAN.md — Mosparo Guard verification core (GUARD-01..04) (Complete: 2026-03-06)
- [x] 02-03-PLAN.md — Security hardening with bypass protection (GUARD-05..07) (Complete: 2026-03-06)

**Research Flag:** 🔍 RESEARCH RECOMMENDED - Complex 11-step Mosparo verification, needs validation testing

---

### Phase 3: Frontend Integration

**Goal:** Template helpers, widget integration, and i18n support

**Depends on:** Phase 2

**Requirements:** FRONT-01, FRONT-02, FRONT-03, FRONT-04, I18N-01, I18N-02, I18N-03

**Success Criteria** (what must be TRUE):
1. Template developer can add `$form->mosparoField()` to render widget
2. Template developer can add `$form->mosparoScript()` to load JS/CSS
3. Mosparo widget initializes automatically when page loads
4. German speakers see translated validation error messages
5. English speakers see translated validation error messages
6. Data attributes (data-mosparo-*) can customize widget behavior

**Plans:** 2 plans

**Plan List:**
- [x] 03-01-PLAN.md — Template helpers (mosparo_field(), $form->mosparoField(), $form->mosparoScript()) (FRONT-01, FRONT-02) (Complete: 2026-03-06)
- [x] 03-02-PLAN.md — Widget snippets with JS/CSS loading and data attributes (FRONT-02, FRONT-03, FRONT-04) (Complete: 2026-03-06)

**Status:** Complete (2/2 plans done)

**Research Flag:** ⚠️ SKIP - Follows established patterns from reCAPTCHA/hCaptcha plugins

---

### Phase 4: Documentation & Release

**Goal:** Installation docs, usage examples, and migration guides

**Depends on:** Phase 3

**Requirements:** DOCS-01, DOCS-02, DOCS-03, DOCS-04

**Success Criteria** (what must be TRUE):
1. User can follow README to install plugin via Composer
2. User can copy-paste template examples from docs into their project
3. User can find all configuration options documented with defaults
4. User migrating from reCAPTCHA/hCaptcha/Turnstile has a step-by-step guide
5. Documentation includes troubleshooting section for common errors

**Plans:** 3 plans

**Plan List:**
- [x] 04-01-PLAN.md — README with installation, quick start, LICENSE, CHANGELOG (DOCS-01) (Complete: 2026-03-07)
- [x] 04-02-PLAN.md — Configuration reference and usage examples (DOCS-02, DOCS-03) (Complete: 2026-03-07)
- [x] 04-03-PLAN.md — Migration guide and troubleshooting (DOCS-04) (Complete: 2026-03-07)

**Research Flag:** ⚠️ SKIP - Standard documentation patterns

---

## Progress

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Foundation | 1/3 | In Progress | 2026-03-06 |
| 2. Core Guard | 3/3 | Complete | 2026-03-06 |
| 3. Frontend Integration | 2/2 | Complete | 2026-03-06 |
| 4. Documentation & Release | 3/3 | Complete | 2026-03-07 |

---

## Phase Dependencies

```
Phase 1: Foundation
    ↓ (provides build/test infrastructure)
Phase 2: Core Guard
    ↓ (provides verification logic)
Phase 3: Frontend Integration
    ↓ (provides complete plugin)
Phase 4: Documentation & Release
```

**Key Dependency:** Phase 3 requires Phase 2 verification working—frontend tokens need backend to verify.

---

## Coverage

| Requirement | Phase | Status |
|-------------|-------|--------|
| INFRA-01 | Phase 1 | Complete |
| INFRA-02 | Phase 1 | Complete |
| INFRA-03 | Phase 1 | Complete |
| INFRA-04 | Phase 1 | Pending |
| CONFIG-01 | Phase 2 | Pending |
| CONFIG-02 | Phase 2 | Pending |
| CONFIG-03 | Phase 2 | Pending |
| CONFIG-04 | Phase 2 | Pending |
| CONFIG-05 | Phase 2 | Pending |
| GUARD-01 | Phase 2 | Pending |
| GUARD-02 | Phase 2 | Pending |
| GUARD-03 | Phase 2 | Pending |
| GUARD-04 | Phase 2 | Pending |
| GUARD-05 | Phase 2 | Complete |
| GUARD-06 | Phase 2 | Complete |
| GUARD-07 | Phase 2 | Complete |
| FRONT-01 | Phase 3 | Complete |
| FRONT-02 | Phase 3 | Complete |
| FRONT-03 | Phase 3 | Pending |
| FRONT-04 | Phase 3 | Pending |
| I18N-01 | Phase 3 | Complete |
| I18N-02 | Phase 3 | Complete |
| I18N-03 | Phase 3 | Complete |
| DOCS-01 | Phase 4 | Complete |
| DOCS-02 | Phase 4 | Complete |
| DOCS-03 | Phase 4 | Complete |
| DOCS-04 | Phase 4 | Complete |

**Coverage:** 25/25 v1 requirements mapped ✓  
**Unmapped:** 0

---

## Gap Analysis

No gaps identified. All success criteria are supported by mapped requirements.

**Cross-check per phase:**
- Phase 1: 4 requirements → 4 criteria ✓
- Phase 2: 12 requirements → 7 criteria ✓
- Phase 3: 7 requirements → 6 criteria ✓
- Phase 4: 4 requirements → 5 criteria ✓

---

*Last updated: 2026-03-07 after Phase 4 Plan 02 completion*
