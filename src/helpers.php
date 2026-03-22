<?php

/**
 * Helper functions for Mosparo integration.
 *
 * Provides template helper functions to easily render Mosparo widget
 * fields and script tags in Kirby Uniform forms.
 *
 * @package Uniform\Mosparo
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

use Kirby\Cms\App;
use Uniform\Mosparo\Config\ConfigFactory;

if (!function_exists('mosparo_field')) {
    /**
     * Render Mosparo widget field.
     *
     * Returns HTML for the Mosparo widget placeholder div with data attributes
     * for initialization. If Mosparo is not configured, returns an empty string
     * (silent fail) for production safety.
     *
     * @param array<string, mixed> $options Widget options:
     *   - id: HTML id attribute for the widget container
     *   - class: Additional CSS classes for the widget container
     *   - data-*: Custom data attributes (e.g., 'data-mosparo-theme' => 'light')
     * @return string HTML for the Mosparo widget or empty string if not configured
     */
    function mosparo_field(array $options = []): string
    {
        try {
            $config = ConfigFactory::fromKirbyOptions();
        } catch (\Throwable $e) {
            // Silent fail if configuration cannot be loaded
            return '';
        }

        // Check if Mosparo is properly configured
        if (!$config->isConfigured()) {
            return '';
        }

        $id = $options['id'] ?? 'mosparo-field';
        $class = $options['class'] ?? 'mosparo-box';

        // Build data attributes
        $dataAttributes = [
            'data-mosparo-uuid' => $config->getUuid(),
            'data-mosparo-public-key' => $config->getPublicKey(),
        ];

        // Add custom data attributes from options
        foreach ($options as $key => $value) {
            if (str_starts_with($key, 'data-')) {
                $dataAttributes[$key] = $value;
            }
        }

        // Build data attributes string
        $dataAttributesHtml = '';
        foreach ($dataAttributes as $attr => $value) {
            if ($value !== null && $value !== '') {
                $dataAttributesHtml .= ' ' . $attr . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        return sprintf(
            '<div id="%s" class="%s"%s></div>',
            htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            $dataAttributesHtml
        );
    }
}

if (!function_exists('mosparo_script')) {
    /**
     * Render Mosparo script tag.
     *
     * Returns HTML script tag to load the Mosparo frontend JavaScript
     * from the configured Mosparo host. If Mosparo is not configured,
     * returns an empty string (silent fail) for production safety.
     *
     * @param array<string, mixed> $options Script options:
     *   - async: Set to false to disable async loading (default: true)
     *   - defer: Set to false to disable defer loading (default: true)
     *   - id: HTML id attribute for the script tag
     * @return string HTML script tag or empty string if not configured
     */
    function mosparo_script(array $options = []): string
    {
        try {
            $config = ConfigFactory::fromKirbyOptions();
        } catch (\Throwable $e) {
            // Silent fail if configuration cannot be loaded
            return '';
        }

        // Check if Mosparo is properly configured
        if (!$config->isConfigured()) {
            return '';
        }

        $host = $config->getHost();
        $async = $options['async'] ?? true;
        $defer = $options['defer'] ?? true;
        $id = $options['id'] ?? null;

        // Normalize host URL (remove trailing slash if present)
        $host = rtrim($host, '/');
        $scriptUrl = $host . '/build/mosparo-frontend.js';

        // Build script tag
        $attributes = [
            'src' => $scriptUrl,
        ];

        if ($async) {
            $attributes['async'] = null;
        }

        if ($defer) {
            $attributes['defer'] = null;
        }

        if ($id !== null) {
            $attributes['id'] = $id;
        }

        // Build attributes string
        $attrsHtml = '';
        foreach ($attributes as $attr => $value) {
            if ($value === null) {
                $attrsHtml .= ' ' . $attr;
            } else {
                $attrsHtml .= ' ' . $attr . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        return sprintf('<script%s></script>', $attrsHtml);
    }
}
