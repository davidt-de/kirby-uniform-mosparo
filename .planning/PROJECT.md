# Kirby Uniform Mosparo Plugin

## What This Is

A Kirby CMS plugin that integrates Mosparo spam protection with the Uniform form plugin. Following the same API patterns as existing Uniform CAPTCHA plugins (reCAPTCHA, hCaptcha, Turnstile), it provides invisible and checkbox-based spam protection for Kirby forms. Built for Kirby 4/5 compatibility and designed for community distribution.

## Core Value

Users can protect their Kirby Uniform forms from spam using Mosparo's privacy-friendly, GDPR-compliant protection without relying on Google or Cloudflare services.

## Requirements

### Validated

(None yet — ship to validate)

### Active

- [ ] Mosparo JavaScript integration (frontend widget)
- [ ] Server-side validation via Mosparo API
- [ ] Uniform guard integration following existing plugin patterns
- [ ] Kirby 4/5 compatibility
- [ ] Composer installation support
- [ ] Configuration via Kirby config
- [ ] Multiple validation modes (checkbox, invisible)
- [ ] Error handling and user feedback
- [ ] Multi-language support (German/English minimum)
- [ ] Documentation and usage examples

### Out of Scope

- Custom Mosparo widget styling (use Mosparo's defaults)
- Built-in Mosparo server setup (assumes external Mosparo instance)
- Real-time validation before form submission
- Advanced statistics dashboard (defer to v2)

## Context

**Target platform:** Kirby CMS 4.x and 5.x  
**Base plugin:** mzur/kirby-uniform (form handling)  
**Service:** mosparo.io (self-hostable spam protection)  
**Reference implementations:**
- eXpl0it3r/kirby-uniform-recaptcha
- lukasleitsch/kirby-uniform-hcaptcha  
- anselmh/kirby-uniform-turnstile

**Key differentiators from existing plugins:**
- Privacy-focused (GDPR-compliant by design)
- Self-hostable (no third-party data sharing)
- Open source spam protection
- European alternative to Google/Cloudflare

**Technical environment:**
- PHP 8.1+ (Kirby 4 requirement)
- JavaScript frontend integration
- REST API communication with Mosparo
- Composer package distribution

## Constraints

- **Tech stack:** PHP 8.1+, JavaScript, Kirby Plugin API
- **Compatibility:** Must work with Kirby 4.x and 5.x
- **Dependencies:** Requires mzur/kirby-uniform
- **API:** Must follow Uniform's Guard pattern like existing CAPTCHA plugins
- **Standards:** PSR-4 autoloading, semantic versioning
- **Privacy:** No tracking, no external data sharing beyond Mosparo instance

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Follow existing plugin patterns | Easier adoption, consistent API for Uniform users | — Pending |
| Kirby 4/5 compatibility | Support current and next major version | — Pending |
| Composer-only distribution | Standard for Kirby plugins | — Pending |
| Support both checkbox and invisible modes | Cover main use cases from Mosparo | — Pending |

---
*Last updated: 2025-03-06 after initialization*
