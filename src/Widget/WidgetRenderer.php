<?php

/**
 * WidgetRenderer - Mosparo Widget Rendering
 *
 * Server-side rendering logic for Mosparo spam protection widget.
 * Provides methods to render the widget container and load required scripts.
 *
 * @package Uniform\Mosparo\Widget
 * @author Patrick Davidt <hallo@davidt.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Widget;

use Uniform\Mosparo\Config\Config;
use Uniform\Mosparo\Config\ConfigFactory;
use Kirby\Toolkit\I18n;

/**
 * Widget renderer for Mosparo spam protection.
 *
 * This class provides static methods for rendering the Mosparo widget
 * container and loading the required JavaScript/CSS files.
 *
 * @package Uniform\Mosparo\Widget
 */
class WidgetRenderer
{
    /**
     * Render the Mosparo widget container
     *
     * @param array<string, mixed> $options Rendering options (id, class, data-attributes)
     * @return string HTML widget container
     */
    public static function render(array $options = []): string
    {
        $config = ConfigFactory::fromKirbyOptions();
        
        if (!$config->isConfigured()) {
            return '<!-- Mosparo: Not configured -->';
        }

        $id = $options['id'] ?? 'mosparo-box';
        $class = $options['class'] ?? 'mosparo-box';
        
        // Build data attributes
        $dataAttributes = self::getDataAttributes($config, $options);
        
        // Render container
        $html = sprintf(
            '<div id="%s" class="%s"%s></div>',
            htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            $dataAttributes
        );
        
        return $html;
    }

    /**
     * Render the Mosparo script tag
     *
     * @param array<string, mixed> $options Script options (async, defer)
     * @return string HTML script tag
     */
    public static function renderScript(array $options = []): string
    {
        $config = ConfigFactory::fromKirbyOptions();
        
        if (!$config->isConfigured()) {
            return '<!-- Mosparo: Not configured -->';
        }

        $host = rtrim($config->getHost() ?? '', '/');
        $async = isset($options['async']) && $options['async'] !== false ? ' async' : '';
        $defer = isset($options['defer']) && $options['defer'] !== false ? ' defer' : '';
        
        $html = sprintf(
            '<script src="%s/build/mosparo-frontend.js"%s%s></script>',
            htmlspecialchars($host, ENT_QUOTES, 'UTF-8'),
            $async,
            $defer
        );
        
        return $html;
    }

    /**
     * Render the Mosparo initialization script
     *
     * Generates JavaScript to initialize the Mosparo widget after the
     * frontend library has been loaded.
     *
     * @param array<string, mixed> $options Initialization options (id, loadCssResource, customOptions)
     * @return string HTML script tag with initialization code
     */
    public static function renderInitScript(array $options = []): string
    {
        $config = ConfigFactory::fromKirbyOptions();
        
        if (!$config->isConfigured()) {
            return '<!-- Mosparo: Not configured -->';
        }

        $id = $options['id'] ?? 'mosparo-box';
        $loadCssResource = $options['loadCssResource'] ?? true;
        $customOptions = $options['customOptions'] ?? [];
        
        // Build initialization options
        $initOptions = array_merge([
            'uuid' => $config->getUuid(),
            'publicKey' => $config->getPublicKey(),
        ], $customOptions);
        
        // Add CSS URL if loading CSS resource is enabled
        if ($loadCssResource) {
            $initOptions['loadCssResource'] = true;
        }
        
        $jsonOptions = json_encode($initOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        
        $host = rtrim($config->getHost() ?? '', '/');
        
        $html = sprintf(
            '<script>document.addEventListener("DOMContentLoaded", function() { new mosparo("%s", "%s", "%s", "%s", %s); });</script>',
            htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($host, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($config->getUuid() ?? '', ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($config->getPublicKey() ?? '', ENT_QUOTES, 'UTF-8'),
            $jsonOptions
        );
        
        return $html;
    }

    /**
     * Build data-mosparo-* attributes
     *
     * @param Config $config Mosparo configuration
     * @param array<string, mixed> $options Custom data attributes
     * @return string HTML data attributes string
     */
    private static function getDataAttributes(Config $config, array $options): string
    {
        $attributes = [
            'data-mosparo-uuid' => $config->getUuid(),
            'data-mosparo-public-key' => $config->getPublicKey(),
        ];

        // Add custom CSS URL if configured
        $cssUrl = $config->getCssUrl();
        if ($cssUrl !== null && $cssUrl !== '') {
            $attributes['data-mosparo-css-url'] = $cssUrl;
        }

        // Merge with user-provided data attributes
        if (isset($options['data']) && is_array($options['data'])) {
            foreach ($options['data'] as $key => $value) {
                $attributes['data-mosparo-' . $key] = $value;
            }
        }

        // Build HTML string
        $html = '';
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $html .= sprintf(' %s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
            }
        }
        
        return $html;
    }
}
