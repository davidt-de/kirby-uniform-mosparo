# Configuration Reference

Complete reference for all Mosparo configuration options in Kirby Uniform.

## Table of Contents

- [Introduction](#introduction)
- [Required Options](#required-options)
- [Optional Options](#optional-options)
- [Complete Example](#complete-example)
- [Environment-Specific Configuration](#environment-specific-configuration)
- [Security Notes](#security-notes)

---

## Introduction

Mosparo configuration is managed through Kirby's options system. All settings are prefixed with `davidt-de.uniform-mosparo.` and should be defined in your Kirby site's configuration file.

**Configuration file location:**
- Standard Kirby site: `site/config/config.php`
- Multi-environment: `site/config/config.{environment}.php`

---

## Required Options

These options must be configured for Mosparo to work. If any are missing, the plugin will silently fail (return empty HTML) to prevent breaking your forms.

### `davidt-de.uniform-mosparo.host`

Your Mosparo instance URL.

| Attribute | Value |
|-----------|-------|
| Type | `string` |
| Required | Yes |
| Example | `'https://mosparo.example.com'` |

```php
// config.php
return [
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.example.com',
];
```

**Getting your host URL:**
1. If using mosparo.io: Use your project URL (e.g., `https://your-project.mosparo.io`)
2. If self-hosted: Use your installation URL (e.g., `https://mosparo.yoursite.com`)

---

### `davidt-de.uniform-mosparo.uuid`

Your Mosparo project UUID.

| Attribute | Value |
|-----------|-------|
| Type | `string` |
| Required | Yes |
| Example | `'550e8400-e29b-41d4-a716-446655440000'` |

```php
// config.php
return [
    'davidt-de.uniform-mosparo.uuid' => '550e8400-e29b-41d4-a716-446655440000',
];
```

**Finding your UUID:**
1. Log into your Mosparo dashboard
2. Go to Project Settings
3. Copy the "Project UUID" value

---

### `davidt-de.uniform-mosparo.publicKey`

Public key for frontend widget initialization.

| Attribute | Value |
|-----------|-------|
| Type | `string` |
| Required | Yes |
| Security | Safe to expose in HTML (used by frontend) |

```php
// config.php
return [
    'davidt-de.uniform-mosparo.publicKey' => 'mpk_abc123def456...',
];
```

**Finding your public key:**
1. Log into your Mosparo dashboard
2. Go to Project Settings → API Keys
3. Copy the "Public Key" value

**Note:** This key is safe to include in your HTML. It's used by the Mosparo JavaScript widget to identify your project.

---

### `davidt-de.uniform-mosparo.privateKey`

Private key for server-side API verification.

| Attribute | Value |
|-----------|-------|
| Type | `string` |
| Required | Yes |
| Security | **Keep secret! Never expose in frontend.** |

```php
// config.php
return [
    'davidt-de.uniform-mosparo.privateKey' => 'msk_xyz789abc123...',
];
```

**Finding your private key:**
1. Log into your Mosparo dashboard
2. Go to Project Settings → API Keys
3. Copy the "Private Key" value

**⚠️ Security Warning:**
- Never commit this key to version control
- Never expose it in frontend code or HTML
- Use environment variables or Kirby secrets (see [Security Notes](#security-notes))

---

## Optional Options

These options have sensible defaults and are only needed for customization.

### `davidt-de.uniform-mosparo.ignoredFields`

Fields to exclude from Mosparo verification.

| Attribute | Value |
|-----------|-------|
| Type | `array` |
| Default | `['password', 'password_confirm', 'csrf_token']` |
| Required | No |

```php
// config.php - Add custom ignored fields
return [
    'davidt-de.uniform-mosparo.ignoredFields' => [
        'password',
        'password_confirm',
        'csrf_token',
        'credit_card',
        'ssn',
    ],
];
```

**Why ignore fields?**
- **Passwords:** Never send passwords to third-party services
- **CSRF tokens:** These are for server-side security, not spam protection
- **Sensitive data:** Credit cards, SSNs, health info should stay private

The default ignores common sensitive fields. Add more as needed for your forms.

---

### `davidt-de.uniform-mosparo.cssUrl`

Custom CSS URL for widget styling.

| Attribute | Value |
|-----------|-------|
| Type | `string|null` |
| Default | `null` |
| Required | No |

```php
// config.php - Use custom styling
return [
    'davidt-de.uniform-mosparo.cssUrl' => '/assets/css/mosparo-custom.css',
];
```

**Use cases:**
- Match widget appearance to your site's design
- Override default Mosparo styles
- Implement accessibility improvements

**Note:** This CSS file will be loaded by the Mosparo iframe. See Mosparo documentation for CSS structure.

---

### `davidt-de.uniform-mosparo.debug`

Enable detailed logging for troubleshooting.

| Attribute | Value |
|-----------|-------|
| Type | `boolean` |
| Default | `false` |
| Required | No |

```php
// config.php - Enable debug mode
return [
    'davidt-de.uniform-mosparo.debug' => true,
];
```

**Debug mode enables:**
- Detailed error messages in Kirby logs
- API request/response logging
- Token validation details
- Configuration diagnostics

**⚠️ Warning:** Always disable debug mode in production. Debug logs may contain sensitive information.

---

## Complete Example

Here's a complete configuration with all options:

```php
<?php
// site/config/config.php

return [
    // =========================================================================
    // Mosparo Spam Protection Configuration
    // =========================================================================
    
    // Required: Your Mosparo instance URL
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.example.com',
    
    // Required: Project UUID from Mosparo dashboard
    'davidt-de.uniform-mosparo.uuid' => '550e8400-e29b-41d4-a716-446655440000',
    
    // Required: Public key (safe to expose)
    'davidt-de.uniform-mosparo.publicKey' => 'mpk_abc123def456...',
    
    // Required: Private key (keep secret!)
    'davidt-de.uniform-mosparo.privateKey' => 'msk_xyz789abc123...',
    
    // Optional: Fields to ignore during verification
    'davidt-de.uniform-mosparo.ignoredFields' => [
        'password',
        'password_confirm',
        'csrf_token',
        'honeypot',
    ],
    
    // Optional: Custom CSS URL
    'davidt-de.uniform-mosparo.cssUrl' => null,
    
    // Optional: Debug mode (enable only for troubleshooting)
    'davidt-de.uniform-mosparo.debug' => false,
];
```

---

## Environment-Specific Configuration

Kirby supports different configurations for different environments.

### Development Environment

```php
<?php
// site/config/config.localhost.php

return [
    // Use test Mosparo project for development
    'davidt-de.uniform-mosparo.host' => 'https://mosparo-test.example.com',
    'davidt-de.uniform-mosparo.uuid' => 'dev-project-uuid',
    'davidt-de.uniform-mosparo.publicKey' => 'mpk_dev_...',
    'davidt-de.uniform-mosparo.privateKey' => 'msk_dev_...',
    
    // Enable debug mode for development
    'davidt-de.uniform-mosparo.debug' => true,
];
```

### Production Environment

```php
<?php
// site/config/config.production.php

return [
    // Production Mosparo project
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.example.com',
    'davidt-de.uniform-mosparo.uuid' => 'prod-project-uuid',
    'davidt-de.uniform-mosparo.publicKey' => 'mpk_prod_...',
    'davidt-de.uniform-mosparo.privateKey' => 'msk_prod_...',
    
    // Disable debug mode in production
    'davidt-de.uniform-mosparo.debug' => false,
];
```

### Loading Different Configs

Kirby automatically loads the appropriate config based on the URL:

```php
// site/config/config.php (always loaded first)
// site/config/config.localhost.php (loaded for localhost)
// site/config/config.production.php (loaded for production.com)
```

See [Kirby documentation](https://getkirby.com/docs/guide/configuration) for more details.

---

## Security Notes

### Protecting Your Private Key

The private key must be kept secret. Here are best practices:

#### Option 1: Environment Variables (Recommended)

```php
<?php
// site/config/config.php

return [
    'davidt-de.uniform-mosparo.host' => 'https://mosparo.example.com',
    'davidt-de.uniform-mosparo.uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'davidt-de.uniform-mosparo.publicKey' => 'mpk_abc123...',
    'davidt-de.uniform-mosparo.privateKey' => getenv('MOSPARO_PRIVATE_KEY'),
];
```

Set the environment variable in your server configuration:

```bash
# .htaccess (Apache)
SetEnv MOSPARO_PRIVATE_KEY "msk_xyz789..."

# nginx.conf (NGINX)
fastcgi_param MOSPARO_PRIVATE_KEY "msk_xyz789...";

# .env file (if using dotenv library)
MOSPARO_PRIVATE_KEY=msk_xyz789...
```

#### Option 2: Kirby Secrets

Kirby 4+ supports a secrets file:

```php
<?php
// site/config/secrets.php

return [
    'davidt-de.uniform-mosparo.privateKey' => 'msk_xyz789...',
];
```

Add `secrets.php` to your `.gitignore` and create it during deployment.

#### Option 3: Outside Document Root

Store sensitive config outside the web root:

```php
<?php
// site/config/config.php

$secrets = require '/etc/kirby/secrets.php';

return [
    'davidt-de.uniform-mosparo.privateKey' => $secrets['mosparo_private_key'],
];
```

### What NOT to Do

❌ **Don't commit private keys to Git:**
```bash
# BAD - Never do this
git add config.php  # Contains private key in plain text
git commit -m "Add config"
```

❌ **Don't expose private keys in templates:**
```php
<!-- BAD - Never do this -->
<script>
    const privateKey = '<?= $kirby->option("davidt-de.uniform-mosparo.privateKey") ?>';
</script>
```

❌ **Don't log private keys:**
```php
// BAD - Never do this
error_log('Mosparo config: ' . print_r($config, true));
```

### Verifying Your Setup

Check that your private key is properly protected:

```php
<?php
// In a controller or template

$privateKey = $kirby->option('davidt-de.uniform-mosparo.privateKey');

// Should NOT output the actual key
echo 'Private key configured: ' . ($privateKey ? 'Yes' : 'No');

// Should show masked version
$masked = substr($privateKey, 0, 8) . '...' . substr($privateKey, -4);
echo 'Key preview: ' . $masked;
```

---

## Configuration Checklist

Before going live, verify:

- [ ] `host` points to your Mosparo instance
- [ ] `uuid` matches your Mosparo project
- [ ] `publicKey` is from the Mosparo dashboard
- [ ] `privateKey` is from the Mosparo dashboard
- [ ] `privateKey` is NOT in version control
- [ ] `debug` is set to `false` in production
- [ ] `ignoredFields` includes all sensitive fields
- [ ] Test submission works correctly

---

## Troubleshooting Configuration

### Widget Not Appearing

1. Check that all 4 required options are set
2. Enable debug mode and check Kirby logs
3. Verify Mosparo host URL is accessible

### Verification Failing

1. Verify UUID and keys match your Mosparo project
2. Check that private key hasn't been exposed/changed
3. Ensure server can reach Mosparo host (firewall/proxy issues)

### "Not Configured" Error

The plugin returns empty HTML when not configured. Check:
- All 4 required options are set
- No typos in option names
- Config file is being loaded

---

## Next Steps

- See [Usage Guide](USAGE.md) for template integration examples
- See [Troubleshooting](TROUBLESHOOTING.md) for common issues
- See [Migration Guide](MIGRATION.md) if upgrading from v1.x
