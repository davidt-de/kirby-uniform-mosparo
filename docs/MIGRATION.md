# Migration Guide

This guide helps you migrate from other CAPTCHA services to Mosparo for your Kirby Uniform forms.

## Why Migrate to Mosparo?

**Privacy & GDPR Compliance**
- No tracking cookies or fingerprinting
- Data stays on your server (or EU-hosted if using mosparo.io)
- No data sharing with third-party advertising companies

**Self-Hosting Option**
- Full control over your data
- No vendor lock-in
- Customizable rules and behavior

**Performance**
- Lightweight JavaScript (much smaller than reCAPTCHA)
- Faster page load times
- No external calls until user interaction

---

## Migrating from Google reCAPTCHA

### Overview of Differences

| Aspect | reCAPTCHA v2/v3 | Mosparo |
|--------|----------------|---------|
| **Privacy** | Sends data to Google | Self-hosted or EU-hosted |
| **Tokens** | Single `g-recaptcha-response` | Two tokens: `_mosparo_submitToken` + `_mosparo_validationToken` |
| **User Experience** | Checkbox challenge or invisible | Checkbox or invisible modes |
| **Setup** | Google account required | Mosparo instance setup required |

### Step-by-Step Migration

#### Step 1: Remove reCAPTCHA Script Tags

**Before (reCAPTCHA):**
```html
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
```

**After (Mosparo):**
```html
<?= mosparo_script() ?>
```

#### Step 2: Replace Field Names

**Before (reCAPTCHA):**
```php
<div class="g-recaptcha" data-sitekey="your-site-key"></div>
<!-- Or invisible mode: -->
<button class="g-recaptcha" data-sitekey="your-site-key" data-callback="onSubmit">Submit</button>
```

**After (Mosparo):**
```php
<?= mosparo_field() ?>
```

#### Step 3: Update Guard

**Before (reCAPTCHA):**
```php
use Uniform\Guards\RecaptchaGuard;

$form->guard(RecaptchaGuard::class);
```

**After (Mosparo):**
```php
use Uniform\Mosparo\Guards\MosparoGuard;

$form->guard(MosparoGuard::class);
```

#### Step 4: Configure Mosparo Credentials

**Before (reCAPTCHA in config.php):**
```php
return [
    'uniform.guards.recaptcha.secret' => 'your-secret-key',
];
```

**After (Mosparo in config.php):**
```php
return [
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.yourdomain.com',
    'davidt-de.uniform-mosparo.uuid' => 'your-project-uuid',
    'davidt-de.uniform-mosparo.publicKey' => 'your-public-key',
    'davidt-de.uniform-mosparo.privateKey' => 'your-private-key',
];
```

---

## Migrating from hCaptcha

### Overview of Differences

| Aspect | hCaptcha | Mosparo |
|--------|----------|---------|
| **Privacy** | Sends data to hCaptcha | Self-hosted or EU-hosted |
| **Tokens** | Single `h-captcha-response` | Two tokens: `_mosparo_submitToken` + `_mosparo_validationToken` |
| **Business Model** | Earn cryptocurrency | Open source / Self-hosted |
| **Customization** | Limited | Full CSS and rule control |

### Step-by-Step Migration

#### Step 1: Remove hCaptcha Script Tags

**Before (hCaptcha):**
```html
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
```

**After (Mosparo):**
```html
<?= mosparo_script() ?>
```

#### Step 2: Replace Field Names

**Before (hCaptcha):**
```php
<div class="h-captcha" data-sitekey="your-site-key"></div>
```

**After (Mosparo):**
```php
<?= mosparo_field() ?>
```

#### Step 3: Update Guard

**Before (hCaptcha):**
```php
use Uniform\Guards\HCaptchaGuard;

$form->guard(HCaptchaGuard::class);
```

**After (Mosparo):**
```php
use Uniform\Mosparo\Guards\MosparoGuard;

$form->guard(MosparoGuard::class);
```

#### Step 4: Configure Mosparo Credentials

**Before (hCaptcha in config.php):**
```php
return [
    'uniform.guards.hcaptcha.secret' => 'your-secret-key',
];
```

**After (Mosparo in config.php):**
```php
return [
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.yourdomain.com',
    'davidt-de.uniform-mosparo.uuid' => 'your-project-uuid',
    'davidt-de.uniform-mosparo.publicKey' => 'your-public-key',
    'davidt-de.uniform-mosparo.privateKey' => 'your-private-key',
];
```

---

## Migrating from Cloudflare Turnstile

### Overview of Differences

| Aspect | Turnstile | Mosparo |
|--------|-----------|---------|
| **Privacy** | Sends data to Cloudflare | Self-hosted or EU-hosted |
| **Tokens** | Single `cf-turnstile-response` | Two tokens: `_mosparo_submitToken` + `_mosparo_validationToken` |
| **Integration** | Cloudflare ecosystem | Standalone / Self-hosted |
| **Rules** | Proprietary ML | Configurable rules |

### Step-by-Step Migration

#### Step 1: Remove Turnstile Script Tags

**Before (Turnstile):**
```html
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
```

**After (Mosparo):**
```html
<?= mosparo_script() ?>
```

#### Step 2: Replace Field Names

**Before (Turnstile):**
```php
<div class="cf-turnstile" data-sitekey="your-site-key"></div>
```

**After (Mosparo):**
```php
<?= mosparo_field() ?>
```

#### Step 3: Update Guard

**Before (Turnstile):**
```php
use Uniform\Guards\TurnstileGuard;

$form->guard(TurnstileGuard::class);
```

**After (Mosparo):**
```php
use Uniform\Mosparo\Guards\MosparoGuard;

$form->guard(MosparoGuard::class);
```

#### Step 4: Configure Mosparo Credentials

**Before (Turnstile in config.php):**
```php
return [
    'uniform.guards.turnstile.secret' => 'your-secret-key',
];
```

**After (Mosparo in config.php):**
```php
return [
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.yourdomain.com',
    'davidt-de.uniform-mosparo.uuid' => 'your-project-uuid',
    'davidt-de.uniform-mosparo.publicKey' => 'your-public-key',
    'davidt-de.uniform-mosparo.privateKey' => 'your-private-key',
];
```

---

## Field Mapping Reference

| Service | Token Field(s) | Notes |
|---------|---------------|-------|
| **reCAPTCHA v2** | `g-recaptcha-response` | Single token, challenge-based |
| **reCAPTCHA v3** | `g-recaptcha-response` | Score-based, invisible |
| **hCaptcha** | `h-captcha-response` | Single token, challenge-based |
| **Turnstile** | `cf-turnstile-response` | Single token, mostly invisible |
| **Mosparo** | `_mosparo_submitToken`, `_mosparo_validationToken` | Two tokens required |

**Important:** Mosparo requires both tokens to be submitted together. Unlike other services that use a single response field, Mosparo uses a submit token (identifies the verification attempt) and a validation token (confirms the challenge was completed).

---

## Data Attributes Comparison

Migrating custom options to Mosparo's data attributes:

### Theme/Appearance

**reCAPTCHA:**
```html
<div class="g-recaptcha" data-theme="dark"></div>
```

**hCaptcha:**
```html
<div class="h-captcha" data-theme="dark"></div>
```

**Mosparo:**
```php
<?= mosparo_field([
    'data-mosparo-theme' => 'dark'
]) ?>
```

### Size

**reCAPTCHA:**
```html
<div class="g-recaptcha" data-size="compact"></div>
```

**Mosparo:**
```php
<?= mosparo_field([
    'data-mosparo-size' => 'compact'
]) ?>
```

### Language

**reCAPTCHA (set via URL):**
```html
<script src="https://www.google.com/recaptcha/api.js?hl=de"></script>
```

**Turnstile:**
```html
<div class="cf-turnstile" data-language="de"></div>
```

**Mosparo:**
```php
<?= mosparo_script(['language' => 'de']) ?>
```

### Callback Functions

**reCAPTCHA:**
```html
<div class="g-recaptcha" data-callback="onRecaptchaSuccess"></div>
```

**Turnstile:**
```html
<div class="cf-turnstile" data-callback="onTurnstileSuccess"></div>
```

**Mosparo:**
```php
<?= mosparo_field([
    'data-mosparo-on-success' => 'onMosparoSuccess'
]) ?>
```

---

## Testing After Migration

### 1. Verify Widget Loads

Check your form page and confirm:
- The Mosparo widget appears
- No JavaScript errors in browser console
- Network tab shows successful requests to your Mosparo host

### 2. Test Form Submission

Submit the form and verify:
- Successful submissions work
- Failed verification shows appropriate error message
- Error messages are translated correctly

### 3. Enable Debug Mode

Add to your config to see detailed errors:
```php
'davidt-de.uniform-mosparo.debug' => true
```

Check your PHP error logs for detailed Mosparo API responses.

### 4. Common Gotchas

**Token Field Names:**
- Mosparo uses `_mosparo_submitToken` and `_mosparo_validationToken`
- Other services use different field names
- Update any custom JavaScript that reads these values

**Required Fields:**
Both tokens are required. If either is missing, verification will fail with `mosparo.error.tokens_missing`.

**API Credentials:**
Mosparo requires four configuration values (host, UUID, publicKey, privateKey) versus the single secret key used by other services.

**Self-Hosted Setup:**
If self-hosting Mosparo, ensure your server can reach the Mosparo instance. Check firewall rules and SSL certificates.

**JavaScript Loading:**
Mosparo script must load before the form is submitted. If using AJAX forms, ensure the script is loaded on the page.

---

## Next Steps

- [Configuration Reference](./CONFIGURATION.md) - Learn about all configuration options
- [Usage Examples](./USAGE.md) - See common implementation patterns
- [Troubleshooting](./TROUBLESHOOTING.md) - Solve common issues
