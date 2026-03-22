# Usage Guide

Complete guide for integrating Mosparo spam protection into your Kirby Uniform templates.

## Table of Contents

- [Basic Example](#basic-example)
- [Template Helper Functions](#template-helper-functions)
- [Form Trait Methods](#form-trait-methods)
- [Snippet Usage](#snippet-usage)
- [Advanced Examples](#advanced-examples)
- [Controller Examples](#controller-examples)
- [Troubleshooting Integration](#troubleshooting-integration)

---

## Basic Example

Here's a complete working example from start to finish.

### 1. Controller (`site/controllers/contact.php`)

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

    // Add Mosparo spam protection guard
    $form->guard(MosparoGuard::class);

    if ($kirby->request()->is('POST') && $form->validates()) {
        // Process the form (send email, save to database, etc.)
        // Only reached if Mosparo verification passes
        
        $form->successMessage('Thank you! Your message has been sent.');
    }

    return compact('form');
};
```

### 2. Template (`site/templates/contact.php`)

```php
<?php snippet('header') ?>

<article class="contact">
    <h1><?= $page->title() ?></h1>

    <?php if ($form->success()): ?>
        <div class="alert alert-success">
            <?= $form->success() ?>
        </div>
    <?php else: ?>

        <form method="post" action="<?= $page->url() ?>">
            <!-- CSRF protection -->
            <?= csrf_field() ?>

            <!-- Name field -->
            <div class="field">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" 
                       value="<?= $form->old('name') ?>" required>
                <?php if ($form->error('name')): ?>
                    <div class="error"><?= $form->error('name') ?></div>
                <?php endif ?>
            </div>

            <!-- Email field -->
            <div class="field">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" 
                       value="<?= $form->old('email') ?>" required>
                <?php if ($form->error('email')): ?>
                    <div class="error"><?= $form->error('email') ?></div>
                <?php endif ?>
            </div>

            <!-- Message field -->
            <div class="field">
                <label for="message">Message *</label>
                <textarea id="message" name="message" rows="5" required>
                    <?= $form->old('message') ?>
                </textarea>
                <?php if ($form->error('message')): ?>
                    <div class="error"><?= $form->error('message') ?></div>
                <?php endif ?>
            </div>

            <!-- Mosparo spam protection field -->
            <div class="field">
                <?= mosparo_field(['id' => 'contact-mosparo']) ?>
            </div>

            <!-- General form errors (including Mosparo) -->
            <?php if ($form->error('mosparo')): ?
003e
                <div class="alert alert-error">
                    <?= $form->error('mosparo') ?>
                </div>
            <?php endif ?>

            <!-- Submit button -->
            <button type="submit" class="button">Send Message</button>
        </form>

        <!-- Mosparo script (loads JS/CSS) -->
        <?= mosparo_script() ?>

    <?php endif ?>

</article>

<?php snippet('footer') ?>
```

### Result

You now have a spam-protected contact form:
- ✅ All submissions verified by Mosparo
- ✅ Bot submissions automatically rejected
- ✅ User-friendly error messages
- ✅ No CAPTCHA friction for legitimate users

---

## Template Helper Functions

The plugin provides two global helper functions for templates.

### `mosparo_field(array $options = []): string`

Renders the Mosparo widget container.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | string | `'mosparo-field'` | HTML ID for the container |
| `class` | string | `'mosparo-box'` | CSS class for the container |
| `data-*` | string | - | Custom data attributes |

**Returns:** `string` - HTML for the widget container

**Basic Usage:**

```php
<?= mosparo_field() ?>
<!-- Output: <div id="mosparo-field" class="mosparo-box" data-mosparo-uuid="..." data-mosparo-public-key="..."></div> -->
```

**With Custom ID:**

```php
<?= mosparo_field(['id' => 'contact-mosparo']) ?>
<!-- Output: <div id="contact-mosparo" class="mosparo-box" ...></div> -->
```

**With Custom Styling:**

```php
<?= mosparo_field([
    'id' => 'mosparo-widget',
    'class' => 'spam-protection-widget my-custom-class',
]) ?>
```

**With Data Attributes (Theme):**

```php
<?= mosparo_field([
    'id' => 'contact-mosparo',
    'data-mosparo-theme' => 'dark',
    'data-mosparo-size' => 'compact',
]) ?>
```

**Complete Example:**

```php
<?= mosparo_field([
    'id' => 'newsletter-mosparo',
    'class' => 'mosparo-box newsletter-widget',
    'data-mosparo-theme' => 'dark',
    'data-mosparo-size' => 'compact',
    'data-mosparo-input-style' => 'floating',
]) ?>
```

---

### `mosparo_script(array $options = []): string`

Renders the Mosparo script tag to load JavaScript/CSS.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `async` | bool | `true` | Load script asynchronously |
| `defer` | bool | `true` | Defer script execution |
| `id` | string | `null` | HTML ID for the script tag |

**Returns:** `string` - HTML script tag

**Basic Usage:**

```php
<?= mosparo_script() ?>
<!-- Output: <script src="https://mosparo.example.com/build/mosparo-frontend.js" async defer></script> -->
```

**Without Async:**

```php
<?= mosparo_script(['async' => false]) ?>
<!-- Output: <script src="..." defer></script> -->
```

**Without Defer:**

```php
<?= mosparo_script(['defer' => false]) ?>
<!-- Output: <script src="..." async></script> -->
```

**Synchronous Loading (blocking):**

```php
<?= mosparo_script(['async' => false, 'defer' => false]) ?>
<!-- Output: <script src="..."></script> -->
```

**With Custom ID:**

```php
<?= mosparo_script(['id' => 'mosparo-js']) ?>
<!-- Output: <script id="mosparo-js" src="..." async defer></script> -->
```

---

## Form Trait Methods

If you prefer object-oriented syntax, use the `FormExtensions` trait.

### Setup

```php
<?php
// site/models/contactform.php

use Uniform\Form;
use Uniform\Mosparo\Form\FormExtensions;

class ContactForm extends Form
{
    use FormExtensions;
    
    public function __construct()
    {
        parent::__construct([
            'name' => ['rules' => ['required']],
            'email' => ['rules' => ['required', 'email']],
            'message' => ['rules' => ['required']],
        ]);
    }
}
```

### Usage in Template

```php
<?php
$form = new ContactForm();
$form->guard(MosparoGuard::class);
?=

<form method="post">
    <!-- Regular form fields -->
    <input type="text" name="name" value="<?= $form->old('name') ?>">
    
    <!-- Use trait methods -->
    <?= $form->mosparoField(['id' => 'contact-mosparo']) ?>
    
    <button type="submit">Send</button>
</form>

<?= $form->mosparoScript() ?>
```

### When to Use Trait vs Helpers

| Approach | Best For | Example |
|----------|----------|---------|
| **Helpers** | Simple forms, quick setup | `mosparo_field()` / `mosparo_script()` |
| **Trait** | Custom Form classes, OOP preference | `$form->mosparoField()` |
| **Snippets** | Reusable components, custom themes | `snippet('mosparo-field')` |

---

## Snippet Usage

Snippets provide reusable, customizable components.

### `snippet('mosparo-field', [...])`

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | string | `'mosparo-box'` | Container element ID |
| `class` | string | `'mosparo-box'` | CSS class(es) |
| `data` | array | `[]` | Custom data attributes |

**Basic Usage:**

```php
<?= snippet('mosparo-field') ?>
```

**With Custom ID:**

```php
<?= snippet('mosparo-field', ['id' => 'contact-mosparo']) ?>
```

**With Custom Class:**

```php
<?= snippet('mosparo-field', [
    'class' => 'spam-widget contact-widget',
]) ?>
```

**With Data Attributes:**

```php
<?= snippet('mosparo-field', [
    'id' => 'contact-mosparo',
    'data' => [
        'theme' => 'dark',
        'size' => 'compact',
    ],
]) ?>
<!-- Output includes: data-mosparo-theme="dark" data-mosparo-size="compact" -->
```

---

### `snippet('mosparo-script', [...])`

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `async` | bool | `true` | Async loading |
| `defer` | bool | `true` | Defer execution |

**Basic Usage:**

```php
<?= snippet('mosparo-script') ?>
```

**Synchronous Loading:**

```php
<?= snippet('mosparo-script', [
    'async' => false,
    'defer' => false,
]) ?>
```

---

### Customizing Snippets

You can override snippet defaults by creating your own:

```php
<?php
// site/snippets/mosparo-field.php (custom version)

$id = $id ?? 'mosparo-widget';
$class = $class ?? 'my-widget-class';
$data = $data ?? ['theme' => 'dark'];

echo snippet('mosparo-field', compact('id', 'class', 'data'));
```

---

## Advanced Examples

### Multiple Forms on One Page

When you have multiple forms, each needs a unique Mosparo field ID:

```php
<?php snippet('header') ?>

<!-- Contact Form -->
<section class="contact-form">
    <h2>Contact Us</h2>
    <form method="post" action="?contact=1">
        <input type="text" name="contact_name" placeholder="Your name">
        <?= mosparo_field(['id' => 'contact-mosparo']) ?>
        <button type="submit">Send Message</button>
    </form>
</section>

<!-- Newsletter Form -->
<section class="newsletter-form">
    <h2>Newsletter</h2>
    <form method="post" action="?newsletter=1">
        <input type="email" name="newsletter_email" placeholder="Your email">
        <?= mosparo_field(['id' => 'newsletter-mosparo']) ?>
        <button type="submit">Subscribe</button>
    </form>
</section>

<!-- One script for all forms -->
<?= mosparo_script() ?>

<?php snippet('footer') ?>
```

---

### Conditional Rendering (Environment-Based)

Only show Mosparo in production:

```php
<?php if ($kirby->environment() === 'production'): ?>
    <?= mosparo_field(['id' => 'contact-mosparo']) ?>
    <?= mosparo_script() ?>
<?php else: ?>
    <!-- Disabled in development -->
    <div class="mosparo-disabled-notice">
        [Mosparo disabled in <?= $kirby->environment() ?> mode]
    </div>
<?php endif ?>
```

Or disable in development via config:

```php
// site/config/config.php
return [
    'davidt-de.uniform-mosparo.host' => $kirby->environment() === 'production' 
        ? 'https://mosparo.example.com' 
        : null,
];
```

---

### Custom Data Attributes

Pass custom attributes to customize widget behavior:

```php
<?= mosparo_field([
    'id' => 'contact-mosparo',
    'data-mosparo-theme' => 'dark',
    'data-mosparo-size' => 'compact',
    'data-mosparo-input-style' => 'floating',
    'data-mosparo-border-radius' => '8',
    'data-mosparo-border-width' => '2',
]) ?>
```

Available data attributes depend on your Mosparo version. Check the [Mosparo documentation](https://mosparo.io/docs) for the latest options.

---

### Custom Error Handling

Display Mosparo errors with custom styling:

```php
<?php if ($form->error('mosparo')): ?>
    <div class="alert alert-error mosparo-error">
        <span class="error-icon">🛡️</span>
        <span class="error-message">
            <?= $form->error('mosparo') ?>
        </span>
        <span class="error-help">
            Please complete the spam protection check and try again.
        </span>
    </div>
<?php endif ?>
```

---

## Controller Examples

### Basic Form with MosparoGuard

```php
<?php
// site/controllers/contact.php

use Uniform\Form;
use Uniform\Mosparo\Guards\MosparoGuard;

return function ($kirby, $page) {
    $form = new Form([
        'name' => ['rules' => ['required']],
        'email' => ['rules' => ['required', 'email']],
        'message' => ['rules' => ['required']],
    ]);

    // Add Mosparo protection
    $form->guard(MosparoGuard::class);

    if ($kirby->request()->is('POST') && $form->validates()) {
        // Send email
        $kirby->mail([
            'from' => $form->data('email'),
            'to' => 'hello@example.com',
            'subject' => 'New contact form submission',
            'body' => $form->data('message'),
        ])->send();
        
        $form->successMessage('Thank you! We\'ll be in touch soon.');
    }

    return compact('form');
};
```

---

### Form with Custom Error Messages

```php
<?php
// site/controllers/contact.php

use Uniform\Form;
use Uniform\Mosparo\Guards\MosparoGuard;

return function ($kirby, $page) {
    $form = new Form([
        'name' => ['rules' => ['required']],
        'email' => ['rules' => ['required', 'email']],
    ]);

    // Add Mosparo with custom error message
    $form->guard(MosparoGuard::class, [
        'errorMessage' => 'Please complete the security check to proceed.',
    ]);

    // ... rest of controller

    return compact('form');
};
```

---

### AJAX Form Submission

```php
<?php
// site/controllers/contact.php

use Uniform\Form;
use Uniform\Mosparo\Guards\MosparoGuard;

return function ($kirby, $page) {
    $form = new Form([
        'name' => ['rules' => ['required']],
        'email' => ['rules' => ['required', 'email']],
    ]);

    $form->guard(MosparoGuard::class);

    // Handle AJAX requests
    if ($kirby->request()->is('POST') && $kirby->request()->ajax()) {
        header('Content-Type: application/json');
        
        if ($form->validates()) {
            // Process form
            echo json_encode(['success' => true, 'message' => 'Thank you!']);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => $form->errors(),
            ]);
        }
        exit;
    }

    // Handle regular POST
    if ($kirby->request()->is('POST') && $form->validates()) {
        // Process form
        $form->successMessage('Thank you!');
    }

    return compact('form');
};
```

**AJAX Template (JavaScript):**

```javascript
document.getElementById('contact-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    const response = await fetch('', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
    });
    
    const result = await response.json();
    
    if (result.success) {
        showSuccess(result.message);
    } else {
        showErrors(result.errors);
    }
});
```

---

## Troubleshooting Integration

### Widget Not Appearing

**Symptom:** The Mosparo widget doesn't show on the page.

**Checklist:**

1. **Is Mosparo configured?**
   ```php
   // Add temporarily to template for debugging
   <pre><?php var_dump($kirby->option('davidt-de.uniform-mosparo.host')) ?></pre>
   ```

2. **Check browser console for JS errors**
   - Open DevTools → Console
   - Look for 404 errors on Mosparo JS
   - Check for CSP (Content Security Policy) errors

3. **Verify script is loading:**
   ```php
   // Check HTML source for this:
   <script src="https://your-mosparo.com/build/mosparo-frontend.js" async defer></script>
   ```

4. **Enable debug mode:**
   ```php
   // config.php
   'davidt-de.uniform-mosparo.debug' => true,
   ```
   Then check Kirby's error logs.

---

### Form Submissions Failing

**Symptom:** Form shows "Spam check failed" error.

**Checklist:**

1. **Are Mosparo tokens being submitted?**
   Check browser DevTools → Network → Form POST:
   - Look for `_mosparo_submitToken` in request
   - Look for `_mosparo_validationToken` in request

2. **Verify API keys:**
   - Double-check UUID, public key, and private key in Mosparo dashboard
   - Ensure they match your config exactly

3. **Check server can reach Mosparo:**
   ```php
   // Test in a controller
   $host = $kirby->option('davidt-de.uniform-mosparo.host');
   $response = file_get_contents($host . '/api/v1/health');
   var_dump($response);
   ```

4. **Check Kirby logs:**
   With debug mode enabled, check `site/logs/` or your server's error log.

---

### Debug Mode Usage

Enable debug mode to see detailed information:

```php
// site/config/config.php
return [
    'davidt-de.uniform-mosparo.debug' => true,
];
```

**What gets logged:**
- Configuration validation results
- API request/response details
- Token extraction info
- Verification results

**View logs:**
```bash
# Kirby 4+
tail -f site/logs/debug.log

# Or check PHP error log
tail -f /var/log/php/errors.log
```

**⚠️ Remember:** Disable debug mode in production!

---

## Next Steps

- See [Configuration Reference](CONFIGURATION.md) for all available options
- See [Troubleshooting](TROUBLESHOOTING.md) for common issues and solutions
- See [Migration Guide](MIGRATION.md) if upgrading from an older version
