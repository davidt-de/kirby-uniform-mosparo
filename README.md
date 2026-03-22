# Kirby Uniform Mosparo

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.0-8892BF.svg)](composer.json)
[![Kirby Version](https://img.shields.io/badge/Kirby-3.5%2B%20%7C%204.x%20%7C%205.x-green.svg)](https://getkirby.com)

> Privacy-friendly, GDPR-compliant spam protection for [Kirby Uniform](https://github.com/mzur/kirby-uniform) forms using [Mosparo](https://mosparo.io).

## What is this?

This plugin integrates Mosparo spam protection with Kirby Uniform forms. Mosparo is an open-source, privacy-focused alternative to reCAPTCHA, hCaptcha, and Cloudflare Turnstile.

### Why Mosparo?

- **GDPR Compliant**: No external tracking, no cookies, no personal data collection
- **Privacy First**: Unlike Google reCAPTCHA, Mosparo doesn't track users across the web
- **Open Source**: Self-hostable, transparent code, no vendor lock-in
- **Lightweight**: Minimal JavaScript, fast loading times
- **Customizable**: Match the widget appearance to your site's design

### Key Features

- 🔒 Server-side verification with Mosparo API
- 🛡️ Bypass protection (bots can't skip the widget)
- 🎨 Checkbox and invisible verification modes
- 📝 Automatic handling of password fields and CSRF tokens
- 🌍 German and English translations included
- ⚡ Zero-dependency frontend (loads Mosparo JS directly)
- 🔧 Configurable via Kirby options

## Requirements

- PHP 8.0 or higher
- Kirby CMS 3.5+, 4.x, or 5.x
- [Kirby Uniform](https://github.com/mzur/kirby-uniform) plugin ^5.0
- A Mosparo account ([mosparo.io](https://mosparo.io) or self-hosted)

## Installation

### Via Composer (Recommended)

```bash
composer require davidt-de/kirby-uniform-mosparo
```

The plugin is automatically installed as a `kirby-plugin` type package via Kirby's official plugin installer.

### Via Kirby CLI

```bash
kirby plugin:install davidt-de/kirby-uniform-mosparo
```

### Manual Installation

1. Download the latest release from GitHub
2. Extract to `site/plugins/uniform-mosparo/`
3. Run `composer install` inside the plugin directory

## Quick Start

### 1. Configure Mosparo

Add your Mosparo credentials to `site/config/config.php`:

```php
<?php

return [
    // Mosparo Configuration
    'getkirby-uniform.mosparo.host' => 'https://your-project.mosparo.io',
    'getkirby-uniform.mosparo.uuid' => 'your-project-uuid',
    'getkirby-uniform.mosparo.publicKey' => 'your-public-key',
    'getkirby-uniform.mosparo.privateKey' => 'your-private-key',
];
```

Get your credentials from your Mosparo project dashboard (Settings → API Keys).

### 2. Add the Guard to Your Form

In your form controller, add the Mosparo guard:

```php
<?php

use Uniform\Form;
use Uniform\Mosparo\Guards\MosparoGuard;

return function ($kirby, $page) {
    $form = new Form([
        'name' => [
            'rules' => ['required'],
            'message' => 'Please enter your name',
        ],
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'Please enter a valid email',
        ],
        'message' => [
            'rules' => ['required'],
            'message' => 'Please enter a message',
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->guard(MosparoGuard::class)  // Add Mosparo protection
             ->emailAction([
                 'to' => 'hello@example.com',
                 'from' => 'noreply@example.com',
             ]);
    }

    return compact('form');
};
```

### 3. Add the Widget to Your Template

In your template file (e.g., `templates/contact.php`):

```php
<form method="POST" action="<?= $page->url() ?>">
    <!-- Your form fields -->
    <label>
        Name
        <input type="text" name="name" value="<?= $form->old('name') ?>" required>
    </label>
    
    <label>
        Email
        <input type="email" name="email" value="<?= $form->old('email') ?>" required>
    </label>
    
    <label>
        Message
        <textarea name="message" required><?= $form->old('message') ?></textarea>
    </label>
    
    <!-- Mosparo spam protection widget -->
    <?= $form->mosparoField() ?>
    
    <button type="submit">Send Message</button>
</form>

<!-- Load Mosparo JavaScript -->
<?= $form->mosparoScript() ?>

<!-- Initialize Mosparo widget (required!) -->
<?= $form->mosparoInit() ?>
```

**Important:** The `mosparoInit()` call is required for the widget to work. Unlike some other CAPTCHA services, Mosparo does not auto-detect the widget container and must be explicitly initialized with JavaScript.

That's it! Your form is now protected by Mosparo.

## What is Mosparo?

[Mosparo](https://mosparo.io) is a modern, open-source spam protection system designed with privacy in mind.

### How It Works

1. **Invisible Protection**: Mosparo analyzes user behavior (mouse movements, keystrokes, timing) to distinguish humans from bots
2. **Checkbox Mode**: Users see a simple checkbox (like "I'm not a robot") only when suspicious activity is detected
3. **No Tracking**: Unlike reCAPTCHA, Mosparo doesn't set cookies or track users across websites
4. **GDPR Compliant**: No personal data is collected or stored

### Mosparo vs Alternatives

| Feature | Mosparo | reCAPTCHA | hCaptcha | Turnstile |
|---------|---------|-----------|----------|-----------|
| Open Source | ✅ | ❌ | ❌ | ❌ |
| Self-Hostable | ✅ | ❌ | ❌ | ❌ |
| GDPR Compliant | ✅ | ⚠️ | ⚠️ | ⚠️ |
| No User Tracking | ✅ | ❌ | ❌ | ❌ |
| Free Tier | ✅ | ✅ | ✅ | ✅ |

### Getting a Mosparo Account

You have two options:

1. **Managed Service**: Sign up at [mosparo.io](https://mosparo.io) (free tier available)
2. **Self-Hosted**: Install Mosparo on your own server ([installation guide](https://mosparo.io/docs/installation))

## Documentation

- **[Configuration Reference](docs/CONFIGURATION.md)** – All available options and defaults
- **[Usage Examples](docs/USAGE.md)** – Advanced form patterns and customizations
- **[Migration Guide](docs/MIGRATION.md)** – Switching from reCAPTCHA, hCaptcha, or Turnstile
- **[Troubleshooting](docs/TROUBLESHOOTING.md)** – Common errors and solutions
- **[Changelog](CHANGELOG.md)** – Version history and release notes

### Migrating from Other CAPTCHA Services?

If you're currently using reCAPTCHA, hCaptcha, or Cloudflare Turnstile, check out the [Migration Guide](docs/MIGRATION.md) for step-by-step instructions on switching to Mosparo. The guide includes:

- Side-by-side code comparisons (before/after)
- Field mapping reference for token names
- Data attributes comparison for styling options
- Testing checklist to verify your migration

## Security

- Private keys are **never exposed** to the frontend
- Server-side verification prevents bypass attacks
- Automatic sanitization of form data before API submission
- Error messages don't leak sensitive configuration details

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Made with ❤️ for the Kirby community**

If you find this plugin useful, please consider [starring the repository](https://github.com/davidt-de/kirby-uniform-mosparo) and [sharing your feedback](https://github.com/davidt-de/kirby-uniform-mosparo/issues).
