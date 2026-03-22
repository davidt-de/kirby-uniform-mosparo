<?php

/**
 * FormExtensions trait for Mosparo integration.
 *
 * Provides convenient methods to render Mosparo widget fields and script
 * tags directly from a Uniform Form instance. This trait is designed to be
 * used with the Uniform\Form class.
 *
 * @package Uniform\Mosparo\Form
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Form;

/**
 * Trait for adding Mosparo helper methods to Uniform forms.
 *
 * This trait provides shortcut methods that wrap the global helper functions
 * `mosparo_field()` and `mosparo_script()` for convenient use within
 * Uniform Form instances.
 *
 * Example usage:
 * ```php
 * class MyForm extends Form {
 *     use FormExtensions;
 * }
 *
 * $form = new MyForm();
 * echo $form-> mosparoField(['id' => 'contact-mosparo']);
 * echo $form-> mosparoScript();
 * ```
 *
 * @package Uniform\Mosparo\Form
 */
trait FormExtensions
{
    /**
     * Render Mosparo widget field.
     *
     * Returns HTML for the Mosparo widget placeholder div. If Mosparo is
     * not configured, returns an empty string (silent fail).
     *
     * @param array<string, mixed> $options Widget options:
     *   - id: HTML id attribute for the widget container
     *   - class: Additional CSS classes for the widget container
     *   - data-*: Custom data attributes (e.g., 'data-mosparo-theme' => 'light')
     * @return string HTML for the Mosparo widget or empty string if not configured
     */
    public function mosparoField(array $options = []): string
    {
        return mosparo_field($options);
    }

    /**
     * Render Mosparo script tag.
     *
     * Returns HTML script tag to load the Mosparo frontend JavaScript.
     * If Mosparo is not configured, returns an empty string (silent fail).
     *
     * @param array<string, mixed> $options Script options:
     *   - async: Set to false to disable async loading (default: true)
     *   - defer: Set to false to disable defer loading (default: true)
     *   - id: HTML id attribute for the script tag
     * @return string HTML script tag or empty string if not configured
     */
    public function mosparoScript(array $options = []): string
    {
        return mosparo_script($options);
    }
}
