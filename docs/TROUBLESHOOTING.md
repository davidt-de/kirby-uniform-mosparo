# Troubleshooting Guide

This guide helps you resolve common issues with the Kirby Uniform Mosparo plugin.

## How to Use This Guide

1. Find the symptom that matches your issue
2. Check the likely causes
3. Follow the step-by-step solutions
4. If problems persist, see [Getting Help](#getting-help)

**Tip:** Enable debug mode to get more detailed error information:
```php
// site/config/config.php
return [
    'getkirby-uniform.mosparo.debug' => true,
];
```

---

## Widget Not Loading

### Symptom
The Mosparo widget does not appear on your form page.

### Likely Causes

1. **Mosparo not configured** - Missing or incomplete configuration
2. **Script not included** - The Mosparo JavaScript is not loaded
3. **JavaScript errors** - Console errors preventing widget initialization
4. **CSS conflicts** - Styles hiding the widget

### Solutions

#### Cause 1: Mosparo Not Configured

**Check configuration:**
```php
// Verify all required options are set in site/config/config.php
return [
    'getkirby-uniform.mosparo.host' => 'https://mosparo.yourdomain.com',
    'getkirby-uniform.mosparo.uuid' => 'your-project-uuid',
    'getkirby-uniform.mosparo.publicKey' => 'your-public-key',
    'getkirby-uniform.mosparo.privateKey' => 'your-private-key',
];
```

**Verify in template:**
```php
<?php
// Add temporarily to check configuration
$config = \Uniform\Mosparo\Config\ConfigFactory::fromKirbyOptions();
if (!$config->isConfigured()) {
    echo 'Mosparo is NOT configured properly';
} else {
    echo 'Mosparo configuration OK';
}
?>
```

#### Cause 2: Script Not Included

**Check if script helper is called:**
```php
<!-- In your template header or before closing </body> -->
<?= mosparo_script() ?>
```

**Verify script loads:**
1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Refresh page
4. Look for `api.js` request to your Mosparo host
5. Check that it returns 200 OK

#### Cause 3: JavaScript Errors

**Check browser console:**
1. Open Developer Tools (F12)
2. Go to Console tab
3. Look for red error messages

**Common errors and fixes:**

| Error | Cause | Fix |
|-------|-------|-----|
| `mosparo is not defined` | Script not loaded | Check `mosparo_script()` is called |
| `Cannot read property...` | Wrong host URL | Verify host configuration |
| `UUID is invalid` | Wrong UUID | Check project UUID in Mosparo dashboard |

#### Cause 4: CSS Conflicts

**Check if widget is hidden:**
1. Right-click where widget should appear
2. Select "Inspect"
3. Check CSS for `display: none`, `visibility: hidden`, or `opacity: 0`
4. Check parent containers for `overflow: hidden` cutting off widget

**Common CSS conflicts:**
```css
/* These might hide the widget */
.form-field * { display: none; }  /* Too broad selector */
.captcha { display: none; }       /* Explicit hiding */
```

---

## Form Submissions Failing

### Symptom
Form always shows error after submission, even when widget is completed.

### Likely Causes

1. **Missing tokens** - Widget not completed before submission
2. **Invalid tokens** - Tokens expired or already used
3. **API errors** - Cannot connect to Mosparo server
4. **Bypass detection** - Spam bot detected

### Error Messages

| Error Key | Translation | Meaning |
|-----------|-------------|---------|
| `mosparo.error.tokens_missing` | Spam protection verification failed. Please try again. | Tokens not submitted |
| `mosparo.error.verification_failed` | Spam check failed. Please complete the verification. | Verification rejected |
| `mosparo.error.api_error` | Unable to verify spam protection. Please try again later. | API connection failed |
| `mosparo.error.bypass_detected` | Form submission could not be verified. Please try again. | Tampering detected |
| `mosparo.error.invalid_token` | Invalid verification token. Please refresh the page and try again. | Bad token format |

### Solutions

#### Error: mosparo.error.tokens_missing

**Check form includes both tokens:**
```html
<form method="POST">
    <?= mosparo_field() ?>
    <!-- This generates: -->
    <input type="hidden" name="_mosparo_submitToken" value="...">
    <input type="hidden" name="_mosparo_validationToken" value="...">
    -->
    <button type="submit">Submit</button>
</form>
```

**For AJAX forms:**
```javascript
// Ensure tokens are included in AJAX request
const formData = new FormData(form);
// formData automatically includes hidden inputs

fetch('/submit', {
    method: 'POST',
    body: formData
});
```

#### Error: mosparo.error.verification_failed

**User needs to complete widget:**
- Widget shows "Please verify you're human"
- User clicked submit without clicking checkbox
- Invisible mode triggered suspicious behavior

**Solution:**
- Ensure user completes widget before submit
- Check if `data-mosparo-required="true"` is preventing submission
- For invisible mode, ensure callback triggers form submission

#### Error: mosparo.error.api_error

**Check server connectivity:**
```bash
# From your Kirby server, test connection to Mosparo
curl https://your-mosparo-host.com/api/v1/verification/verify

# Should return 401 Unauthorized (not connection error)
```

**Check firewall/proxy:**
- Verify outbound HTTPS (port 443) is allowed
- Check proxy settings if behind corporate firewall
- Ensure SSL certificates are valid

#### Error: mosparo.error.bypass_detected

**Causes:**
- JavaScript disabled or tampered with
- Hidden field values changed
- Form submitted too quickly (bot behavior)
- Token reuse attempt

**Solutions:**
1. Ensure JavaScript is enabled
2. Don't programmatically modify Mosparo fields
3. Check for browser extensions interfering with forms
4. Refresh page and try again (tokens are single-use)

#### Error: mosparo.error.invalid_token

**Causes:**
- Token format is incorrect
- Token was modified
- Wrong project UUID

**Solutions:**
1. Refresh page to get fresh tokens
2. Verify `uuid` configuration matches Mosparo project
3. Check for JavaScript errors preventing token generation

---

## Configuration Errors

### "Mosparo is not configured" Error

This error occurs when required configuration options are missing.

**Required options:**
```php
// All four are required
'getkirby-uniform.mosparo.host'       // https://mosparo.yourdomain.com
'getkirby-uniform.mosparo.uuid'       // Your project UUID
'getkirby-uniform.mosparo.publicKey'  // Your public key
'getkirby-uniform.mosparo.privateKey' // Your private key
```

**How to verify credentials:**

1. **Host URL:**
   - Must include `https://`
   - No trailing slash
   - Example: `https://mosparo.mysite.com`

2. **UUID:**
   - 36 characters with dashes
   - Format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
   - Found in Mosparo dashboard → Project settings

3. **Public/Private Keys:**
   - Generated when creating Mosparo project
   - Available in Mosparo dashboard → API Keys
   - Don't mix up public vs private

**Test configuration:**
```php
<?php
$config = \Uniform\Mosparo\Config\ConfigFactory::fromKirbyOptions();

echo 'Host: ' . ($config->getHost() ?: 'NOT SET') . "\n";
echo 'UUID: ' . ($config->getUuid() ?: 'NOT SET') . "\n";
echo 'Public Key: ' . ($config->getPublicKey() ? 'SET' : 'NOT SET') . "\n";
echo 'Private Key: ' . ($config->getPrivateKey() ? 'SET' : 'NOT SET') . "\n";
echo 'Configured: ' . ($config->isConfigured() ? 'YES' : 'NO') . "\n";
?>
```

---

## API Connection Issues

### Symptom
"Unable to verify spam protection" error on submission.

### Causes and Solutions

#### Network/Firewall Blocking

**Check connectivity:**
```bash
# Test from your web server
ping your-mosparo-host.com
curl -I https://your-mosparo-host.com
```

**Firewall rules to check:**
- Outbound HTTPS (port 443) must be allowed
- No IP-based blocking
- Proxy settings if behind corporate firewall

#### Wrong Host URL

**Common mistakes:**
```php
// ❌ Wrong - missing protocol
'host' => 'mosparo.mysite.com'

// ❌ Wrong - trailing slash
'host' => 'https://mosparo.mysite.com/'

// ✅ Correct
'host' => 'https://mosparo.mysite.com'
```

#### SSL Certificate Issues

**For self-hosted Mosparo:**
- Certificate must be valid (not self-signed in production)
- Domain must match certificate
- Intermediate certificates must be installed

**Test SSL:**
```bash
openssl s_client -connect your-mosparo-host.com:443 -servername your-mosparo-host.com
```

---

## Debug Mode

### How to Enable

Add to your Kirby configuration:
```php
// site/config/config.php
return [
    'getkirby-uniform.mosparo.debug' => true,
];
```

### What Gets Logged

When debug mode is enabled:
1. API request details are logged
2. Response data is logged
3. Token validation steps are logged
4. Error details include full exception messages

### Where to Find Logs

**Kirby logs:**
- Check `site/logs/` directory
- Or your web server's error log:
  - Apache: `/var/log/apache2/error.log`
  - Nginx: `/var/log/nginx/error.log`

**PHP error log:**
```php
// Add temporarily to see errors
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Understanding Error Messages

**API Error Example:**
```
Mosparo API error: cURL error 28: Connection timed out
```
→ Network issue, check connectivity

**Verification Failed Example:**
```
Verification failed: isSubmittable returned false
```
→ Spam detected or tokens invalid

---

## Getting Help

### Before Reporting an Issue

1. **Check this guide** - Your issue may be covered above
2. **Enable debug mode** - Get detailed error information
3. **Test with minimal setup** - Disable other plugins temporarily
4. **Verify configuration** - Double-check all four required options

### Information to Include

When reporting an issue, include:

**Versions:**
- Kirby version
- Uniform plugin version
- Mosparo plugin version
- PHP version

**Configuration (sanitized):**
```php
// Show structure, NOT actual keys
return [
    'getkirby-uniform.mosparo.host' => 'https://mosparo.example.com',
    'getkirby-uniform.mosparo.uuid' => 'SET',
    'getkirby-uniform.mosparo.publicKey' => 'SET',
    'getkirby-uniform.mosparo.privateKey' => 'SET',
];
```

**Error Details:**
- Full error message
- When it occurs (page load, form submission, etc.)
- Browser console errors (if any)
- PHP error log entries

**Environment:**
- Hosting type (shared, VPS, etc.)
- Mosparo setup (self-hosted or mosparo.io)
- Any special server configuration (proxies, firewalls)

### Where to Report

- **GitHub Issues:** [github.com/davidt-de/kirby-uniform-mosparo](https://github.com/davidt-de/kirby-uniform-mosparo)
- **Kirby Forum:** [forum.getkirby.com](https://forum.getkirby.com)

### Useful Resources

- [Mosparo Documentation](https://mosparo.io/docs/)
- [Kirby Uniform Documentation](https://kirby-uniform.readthedocs.io/)
- [Configuration Reference](./CONFIGURATION.md)
- [Migration Guide](./MIGRATION.md)
