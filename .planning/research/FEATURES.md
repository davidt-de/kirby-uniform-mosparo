# Feature Research: Kirby Uniform Mosparo Plugin

**Domain:** Kirby CMS CAPTCHA Plugin
**Researched:** 2026-03-06
**Confidence:** HIGH

## Feature Landscape

### Table Stakes (Users Expect These)

Features users assume exist. Missing these = product feels incomplete.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| **Magic Method Guard** | Standard Uniform pattern (`mosparoGuard()`) | LOW | Follows `recaptchaGuard()`, `hcaptchaGuard()`, `turnstileGuard()` pattern. Essential for Uniform integration. |
| **Template Helper: Field** | Render Mosparo widget (`mosparoField()`) | LOW | Renders HTML for Mosparo checkbox/field. Must support custom CSS classes. |
| **Template Helper: Script** | Load Mosparo JavaScript (`mosparoScript()`) | LOW | Injects JS from Mosparo instance. Should support async/defer attributes. |
| **Configuration: API Keys** | Site key + secret key required for API | LOW | Standard config pattern: `mosparo.siteKey`, `mosparo.secretKey`. |
| **Configuration: Host URL** | Mosparo is self-hosted; requires host URL | LOW | Different from cloud CAPTCHAs. Config: `mosparo.host` (e.g., `https://mosparo.example.com`). |
| **Composer Installation** | Standard PHP package manager | LOW | `composer require [vendor]/kirby-uniform-mosparo`. All competitors support this. |
| **i18n Support** | Multi-language error messages | MEDIUM | Kirby's i18n system. Required for production use. |
| **Kirby 3 & 4 Compatibility** | Users run both versions | LOW | All three competitor plugins target K3/K4. Must work with both. |

### Differentiators (Competitive Advantage)

Features that set the product apart. Not required, but valuable.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| **Self-Hosted Privacy** | No data leaves your infrastructure (GDPR advantage) | LOW | Major selling point vs Google/Cloudflare. Mosparo's core differentiator. |
| **Custom Verification Rules** | Configure Mosparo rule sets per form | MEDIUM | Mosparo supports custom rules. Expose via plugin config. |
| **Theme Customization** | Match Mosparo widget to site design | LOW | Mosparo supports theming. Config option for theme ID or inline styles. |
| **Debug Mode** | Detailed logging for troubleshooting | LOW | Config flag for verbose logging during development. |
| **Submission Data Caching** | Reduce API calls with local caching | MEDIUM | Cache verification results briefly. Improves performance on high-traffic sites. |
| **Field Mapping** | Map form fields to Mosparo ignored fields | MEDIUM | Mosparo has "ignored fields" concept. Allow users to specify which fields to ignore. |
| **Multiple Instance Support** | Support multiple Mosparo projects | MEDIUM | Allow different forms to use different Mosparo instances. Config per-form or global. |
| **Async Form Support** | Work with AJAX/fetch submissions | MEDIUM | Handle Mosparo validation in API-driven forms. Growing use case. |
| **Blocklist Integration** | Auto-block repeat offenders | MEDIUM | Integrate with Mosparo's blocklist API. Advanced feature. |

### Anti-Features (Commonly Requested, Often Problematic)

Features that seem good but create problems.

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|-----------------|-------------|
| **Fallback to reCAPTCHA** | "Just in case Mosparo fails" | Violates privacy-first approach; adds Google dependency | Document Mosparo reliability, provide clear troubleshooting |
| **Score-Based Validation** | reCAPTCHA v3 style (0.0-1.0) | Mosparo uses pass/fail, not scores. Forcing scores adds complexity | Use Mosparo's built-in rule system for nuanced validation |
| **Invisible Mode (No User Action)** | "Don't make users click" | Mosparo's checkbox is its privacy protection mechanism. Removing it defeats the purpose | Educate users on privacy benefits of visible verification |
| **Admin Dashboard Widget** | "View stats in Kirby Panel" | Adds Panel complexity; Mosparo has its own dashboard | Link to Mosparo dashboard in plugin docs |
| **Automatic Form Detection** | "Auto-inject into all forms" | Magic injection causes unexpected behavior, conflicts with other plugins | Explicit opt-in via template helpers and guards |

## Feature Dependencies

```
[Core Plugin Structure]
    ├──requires──> [Composer Package Definition]
    ├──requires──> [Kirby Plugin Registration]
    └──requires──> [i18n Translation Files]

[Template Helpers]
    ├──requires──> [Configuration: Host URL]
    ├──requires──> [Configuration: Site Key]
    └──enhances──> [Theme Customization]

[Magic Method Guard]
    ├──requires──> [Configuration: Secret Key]
    ├──requires──> [Mosparo API Client]
    └──enhances──> [Debug Mode]

[Custom Verification Rules]
    ├──requires──> [Magic Method Guard]
    └──conflicts──> [Score-Based Validation]

[Multiple Instance Support]
    ├──requires──> [Configuration: Host URL] (per-instance)
    ├──requires──> [Configuration: Site Key] (per-instance)
    └──requires──> [Configuration: Secret Key] (per-instance)

[Async Form Support]
    ├──requires──> [Magic Method Guard]
    └──enhances──> [Debug Mode] (for API debugging)
```

### Dependency Notes

- **Mosparo API Client**: Core dependency for all verification. Should use official Mosparo PHP client if available, or implement lightweight client.
- **Configuration Hierarchy**: Global config → Form-specific config → Runtime options. Must be clearly documented.
- **Theme Customization**: Depends on Mosparo's theming API. Requires theme ID from Mosparo instance.

## MVP Definition

### Launch With (v1.0)

Minimum viable product — what's needed to validate the concept.

- [ ] **Magic Method Guard** (`mosparoGuard()`) — Essential Uniform integration
- [ ] **Template Helper: Field** (`mosparoField()`) — Render the widget
- [ ] **Template Helper: Script** (`mosparoScript()`) — Load required JS
- [ ] **Configuration: Host, Site Key, Secret Key** — Core API credentials
- [ ] **Composer Installation** — Standard distribution
- [ ] **i18n Support** — Basic error messages
- [ ] **Kirby 3 & 4 Compatibility** — Target current versions

### Add After Validation (v1.1-1.x)

Features to add once core is working.

- [ ] **Theme Customization** — Trigger: Users request styling options
- [ ] **Debug Mode** — Trigger: Support requests for troubleshooting
- [ ] **Field Mapping / Ignored Fields** — Trigger: Complex forms need field exclusions
- [ ] **Custom Verification Rules** — Trigger: Power users want rule customization
- [ ] **Multiple Instance Support** — Trigger: Multi-site or multi-client use cases

### Future Consideration (v2+)

Features to defer until product-market fit is established.

- [ ] **Submission Data Caching** — Why defer: Adds complexity; premature optimization
- [ ] **Async Form Support** — Why defer: Niche use case; requires significant JS work
- [ ] **Blocklist Integration** — Why defer: Advanced feature; Mosparo dashboard suffices initially

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| Magic Method Guard | HIGH | LOW | P1 |
| Template Helper: Field | HIGH | LOW | P1 |
| Template Helper: Script | HIGH | LOW | P1 |
| Configuration: API Keys | HIGH | LOW | P1 |
| Configuration: Host URL | HIGH | LOW | P1 |
| Composer Installation | MEDIUM | LOW | P1 |
| i18n Support | MEDIUM | MEDIUM | P1 |
| Kirby 3 & 4 Compatibility | HIGH | LOW | P1 |
| Self-Hosted Privacy Marketing | HIGH | LOW | P1 |
| Theme Customization | MEDIUM | LOW | P2 |
| Debug Mode | MEDIUM | LOW | P2 |
| Field Mapping | MEDIUM | MEDIUM | P2 |
| Custom Verification Rules | MEDIUM | MEDIUM | P2 |
| Multiple Instance Support | LOW | MEDIUM | P2 |
| Submission Data Caching | LOW | MEDIUM | P3 |
| Async Form Support | MEDIUM | MEDIUM | P3 |
| Blocklist Integration | LOW | HIGH | P3 |

**Priority key:**
- P1: Must have for launch
- P2: Should have, add when possible
- P3: Nice to have, future consideration

## Competitor Feature Analysis

| Feature | reCAPTCHA | hCaptcha | Turnstile | Our Approach |
|---------|-----------|----------|-----------|--------------|
| **Plugin Type** | Cloud (Google) | Cloud (Intuition Machines) | Cloud (Cloudflare) | Self-hosted (Mosparo) |
| **Privacy** | ❌ Google tracking | ⚠️ US company | ⚠️ Cloudflare | ✅ Self-hosted, GDPR-friendly |
| **Template Helper** | `recaptchaButton()` + `recaptchaScript()` | `hcaptchaField()` + `hcaptchaScript()` | `turnstileField()` + `turnstileScript()` | `mosparoField()` + `mosparoScript()` |
| **Controller Guard** | `recaptchaGuard()` | `hcaptchaGuard()` | `turnstileGuard()` | `mosparoGuard()` |
| **Configuration Keys** | siteKey, secretKey, acceptableScore | siteKey, secretKey | siteKey, secretKey, theme | siteKey, secretKey, host, theme |
| **Scoring System** | ✅ 0.0-1.0 score | ❌ Pass/fail | ❌ Pass/fail | ❌ Pass/fail (by design) |
| **JavaScript Required** | ✅ Yes (v3) | ✅ Yes | ✅ Yes | ✅ Yes |
| **i18n Support** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes (follow pattern) |
| **Composer** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes (match) |
| **Theme Support** | ❌ Limited | ✅ Yes | ✅ Yes (light/dark) | ✅ Yes (via Mosparo) |
| ** Kirby Versions** | 3 & 4 | 3 | 3 & 4 | 3 & 4 (match) |
| **Custom Rules** | ❌ No | ❌ No | ❌ No | ✅ Yes (Mosparo advantage) |

## Mosparo-Specific Considerations

### Unique Advantages to Highlight

1. **Data Sovereignty**: Unlike competitors, Mosparo doesn't send user data to third parties
2. **Custom Rules**: Mosparo's rule system allows complex validation logic (competitors don't offer this)
3. **Transparent Pricing**: Self-hosted = no per-request costs
4. **Full Control**: Admin owns the verification data and logic

### Implementation Differences

| Aspect | Cloud CAPTCHAs | Mosparo |
|--------|----------------|---------|
| **API Endpoint** | Fixed (google.com, hcaptcha.com, etc.) | Configurable (user's instance) |
| **Validation Flow** | Submit token → Verify with cloud service | Submit token → Verify with self-hosted instance |
| **Error Handling** | Standard HTTP errors | May include Mosparo-specific error codes |
| **Rate Limiting** | Service-imposed | User-controlled via Mosparo config |

## Sources

- [eXpl0it3r/kirby-uniform-recaptcha](https://github.com/eXpl0it3r/kirby-uniform-recaptcha) — reCAPTCHA plugin (Kirby 3 & 4)
- [lukasleitsch/kirby-uniform-hcaptcha](https://github.com/lukasleitsch/kirby-uniform-hcaptcha) — hCaptcha plugin (Kirby 3)
- [anselmh/kirby-uniform-turnstile](https://github.com/anselmh/kirby-uniform-turnstile) — Cloudflare Turnstile plugin (Kirby 3 & 4)
- [Mosparo Plugin Integration Docs](https://documentation.mosparo.io/docs/integration/with_plugins) — Official Mosparo plugin patterns
- [Uniform Plugin Guards Documentation](https://kirby-uniform.readthedocs.io/en/latest/guards/guards/) — Magic method guard pattern

---
*Feature research for: Kirby Uniform Mosparo Plugin*
*Researched: 2026-03-06*
