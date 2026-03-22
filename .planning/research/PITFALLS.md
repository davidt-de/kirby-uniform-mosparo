# Pitfalls Research: Kirby Uniform Mosparo Plugin

**Domain:** Kirby CMS Plugin Development + Mosparo CAPTCHA Integration  
**Researched:** 2026-03-06  
**Confidence:** MEDIUM (based on official docs and GitHub issues; some community patterns may vary)

---

## Critical Pitfalls

### Pitfall 1: Frontend-Only Validation (No Server-Side Verification)

**What goes wrong:**
Form submissions pass the Mosparo checkbox but never verify the submission server-side. Attackers can bypass protection by submitting directly to the endpoint without JavaScript validation.

**Why it happens:**
Developers assume the checkbox click is sufficient protection. Mosparo documentation explicitly warns: "From a purely technical point of view, it is possible that someone passes the validation by mosparo, then changes the request again with technical tools, and then submits the form."

**How to avoid:**
- Always implement the 11-step verification process in the Kirby backend
- Use the official PHP API client or implement manual verification
- Verify the `verificationSignature` matches your calculated signature
- Confirm ALL required fields are present in `verifiedFields` response

**Warning signs:**
- No API calls to Mosparo in controller/guard code
- Form processing happens before Mosparo validation
- Missing validation of `_mosparo_submitToken` and `_mosparo_validationToken`

**Phase to address:** Phase 2 (Core Guard Implementation)

---

### Pitfall 2: Field Name Mismatch Between Frontend and Backend

**What goes wrong:**
Mosparo validation fails because form field names in HTML don't match the keys sent to the Mosparo API. This causes all submissions to be rejected as spam.

**Why it happens:**
- Kirby's form handling may transform field names (e.g., adding prefixes, array notation)
- Frameworks like WordPress escape characters (`'`, `"`) which changes field values
- Nested fields like `form[address][street]` must maintain exact structure

**How to avoid:**
- Use exact field names from HTML `name` attributes
- For nested fields, preserve the exact array structure
- Replace CRLF with LF line breaks (`\r\n` → `\n`) before hashing
- Test that `ksort()` produces identical ordering on both ends

**Warning signs:**
- Mosparo returns `valid: true` but fields marked as `invalid`
- Specific fields consistently fail verification
- Debug output shows different field names than expected

**Phase to address:** Phase 2 (Core Guard Implementation)

---

### Pitfall 3: Ignoring Mosparo's Ignored Fields List

**What goes wrong:**
Form includes fields that Mosparo doesn't validate (checkboxes, radio buttons, passwords, hidden fields) but the backend sends them for verification anyway. This causes signature mismatches.

**Why it happens:**
Developers don't realize Mosparo has a specific list of ignored field types. The custom integration docs explicitly state: "mosparo does not validate field types like checkbox, radio, password, and hidden."

**How to avoid:**
- Strip ignored fields before generating form data signature
- Refer to Mosparo's official ignored fields list
- Implement field filtering in the verification process

**Warning signs:**
- Verification fails when form includes checkboxes or file uploads
- Signature mismatch despite seemingly correct implementation

**Phase to address:** Phase 2 (Core Guard Implementation)

---

### Pitfall 4: Missing Bypass Protection Checks

**What goes wrong:**
Users can bypass Mosparo entirely by manipulating form fields—changing required fields to ignored field types or removing the Mosparo tokens.

**Why it happens:**
The Mosparo response only includes fields it verified. If a required field isn't in `verifiedFields`, it means the user tampered with the form (e.g., changed a text field to a hidden field).

**How to avoid:**
After successful Mosparo verification, explicitly check:
```php
if (!isset($responseData['verifiedFields']['name']) || 
    !isset($responseData['verifiedFields']['email'])) {
    return false; // Someone tampered with the form
}
```

**Warning signs:**
- Spam submissions with empty required fields
- Form data missing expected fields
- Inconsistent field presence in submissions

**Phase to address:** Phase 2 (Core Guard Implementation)

---

### Pitfall 5: Uniform Plugin Integration Anti-Patterns

**What goes wrong:**
Custom guard doesn't properly integrate with Kirby Uniform's lifecycle, causing validation to run at wrong times or bypass Uniform's built-in guards.

**Why it happens:**
From Kirby Uniform GitHub issues (#262, #259), common problems include:
- Guards running in wrong order
- Form data not available when guard executes
- Multiple file uploads not handled correctly

**How to avoid:**
- Study Uniform's guard interface and lifecycle hooks
- Implement `__invoke()` method following Uniform's pattern
- Ensure Mosparo validation happens at the right stage
- Test with Uniform's form reset and re-submit scenarios

**Warning signs:**
- Guard never executes
- Guard executes but form data is empty
- Form validates but Mosparo check is skipped

**Phase to address:** Phase 3 (Uniform Integration)

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Hardcode Mosparo credentials | Faster setup | Security risk, no environment flexibility | Never in production |
| Skip verification signature check | Simpler code | Attackers can forge responses | Never |
| Ignore specific field validation failures | More lenient form | Spam gets through | Never |
| Use visible mode only | Easier to implement | Poor UX on mobile, accessibility issues | Only for MVP testing |
| Omit translation support | Faster development | Limited international adoption | MVP only, add before v1.0 |

---

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| **Mosparo CSS** | Inline CSS in template | Use `loadCssResource: true` option or proper CSS link tag |
| **Mosparo initialization** | Initialize on every page load | Only initialize when form is present |
| **Token extraction** | Assume tokens exist | Always check for `_mosparo_submitToken` and `_mosparo_validationToken` before processing |
| **API error handling** | Treat API errors as spam failures | Distinguish between "spam detected" and "API error" — allow retry on errors |
| **Invisible mode** | Forget to enable in design settings | Must switch to "invisible spam protection" design mode in Mosparo backend first |
| **Field preparation** | Escape/sanitize before hashing | Hash raw user input; escaping changes the hash |

---

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Synchronous API calls | Form submission hangs | Use async requests with proper timeout handling | Any network latency |
| No Mosparo timeout | Page loads forever if Mosparo is down | Set reasonable timeout (5-10s) and fail gracefully | Mosparo service disruption |
| No caching of public keys | Repeated config lookups | Cache project UUID and keys in Kirby config | High-traffic sites |
| Verifying on every request | Slow page loads | Only verify on POST requests | Any form page |
| Loading Mosparo JS on all pages | Unnecessary HTTP requests | Conditionally load only on pages with forms | Sites with many pages |

---

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Expose private key in frontend | Attackers can forge verification signatures | Keep private key server-side only; only public key in JS |
| Trust frontend-reported validation | Bypass attacks | Always verify server-side; frontend is only UX |
| Log form data with tokens | Token replay attacks | Don't log `_mosparo_*` tokens; they're single-use |
| No HTTPS on Mosparo endpoint | Man-in-the-middle attacks | Always use HTTPS for Mosparo host |
| Missing CSP headers | XSS via Mosparo widget | Implement Content Security Policy (see Mosparo CSP docs) |
| Reuse submit tokens | Replay attacks | Submit tokens are single-use; don't cache or retry with same token |

---

## UX Pitfalls

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| No loading state | Users click multiple times | Show spinner while Mosparo validates |
| No error messages | Users don't know why form failed | Display Mosparo error messages (`errorSpamDetected`, `errorLockedOut`, etc.) |
| Invisible mode without fallback | Broken if JS fails | Provide visible mode fallback for no-JS scenarios |
| Wrong language | Non-English users see English | Use `language` option or rely on browser detection |
| No accessibility labels | Screen reader users confused | Include Mosparo's accessibility messages |
| Blocking form on API error | Legitimate users rejected | Fail open (allow submission) on Mosparo API errors with logging |

---

## "Looks Done But Isn't" Checklist

- [ ] **Server-side verification:** Frontend checkbox works, but does backend verify with Mosparo API?
- [ ] **Signature validation:** Is the `verificationSignature` from Mosparo compared to calculated signature?
- [ ] **Field presence check:** Are ALL required fields verified to be in `verifiedFields` response?
- [ ] **Ignored fields handling:** Are checkbox, radio, password, hidden fields excluded from verification?
- [ ] **Error handling:** Does the plugin handle API errors, timeouts, and network failures gracefully?
- [ ] **Token cleanup:** Are `_mosparo_*` tokens stripped from logged/saved form data?
- [ ] **Multi-language support:** Does the plugin handle non-English sites correctly?
- [ ] **Accessibility:** Are screen reader messages and ARIA labels implemented?
- [ ] **CSP compatibility:** Does it work with strict Content Security Policy headers?

---

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| No server-side verification | HIGH | Add verification to all form endpoints; audit past submissions for spam |
| Private key leaked | HIGH | Rotate keys in Mosparo project settings; update all installations |
| Field name mismatches | MEDIUM | Fix field name mapping; re-test all form variations |
| Missing bypass protection | MEDIUM | Add field presence checks; monitor for suspicious submissions |
| Performance issues | LOW | Add caching, timeouts, and conditional loading |

---

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Frontend-only validation | Phase 2 | Code review: API client usage in guard |
| Field name mismatch | Phase 2 | Test with complex field names (arrays, nested) |
| Ignored fields not filtered | Phase 2 | Test with checkbox/radio/password fields |
| Missing bypass protection | Phase 2 | Unit test: submit with manipulated field types |
| Uniform integration issues | Phase 3 | Integration test with full Uniform form lifecycle |
| No error handling | Phase 3 | Test with Mosparo offline / network timeout |
| Missing translations | Phase 4 | Test with non-English Kirby installations |
| CSP issues | Phase 4 | Test with strict CSP headers enabled |
| Performance traps | Phase 4 | Load testing with slow Mosparo responses |

---

## Domain-Specific Considerations

### Kirby Uniform Context

From Kirby Uniform GitHub issues analysis:

1. **Issue #262 (Multiple file uploads):** File upload handling requires special care in guards—Mosparo doesn't validate file contents, only field metadata
2. **Issue #259 (Honeypot labels):** If combining Mosparo with Uniform's honeypot, ensure proper labeling for accessibility
3. **Issue #158 (Translations):** Incomplete translations are a common pain point—plan for full i18n from the start

### Mosparo Integration Specifics

From Mosparo custom integration documentation:

1. **Verification is 11 steps:** Skipping any step breaks security
2. **Hash algorithm matters:** Must use SHA256 for field hashing, HMAC SHA256 for signatures
3. **Token lifecycle:** Submit tokens are single-use; validation tokens persist for the session
4. **Design mode requirement:** Invisible mode requires explicit enablement in Mosparo backend

---

## Sources

- [Kirby Uniform GitHub Issues](https://github.com/mzur/kirby-uniform/issues) — Real-world problems with form guards
- [Mosparo Custom Integration Docs](https://documentation.mosparo.io/docs/integration/custom) — Official integration requirements and 11-step verification process
- [Kirby Plugin Best Practices](https://getkirby.com/docs/guide/plugins/best-practices) — Plugin structure and conventions
- [Kirby Plugin Installation Guide](https://getkirby.com/docs/guide/plugins/installing-plugins) — Installation methods and compatibility

---

*Pitfalls research for: Kirby Uniform Mosparo Plugin*  
*Researched: 2026-03-06*
