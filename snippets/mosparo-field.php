<?php
/**
 * Mosparo Widget Field Snippet
 *
 * Renders the Mosparo spam protection widget container.
 * Requires the mosparo-script snippet to load JS/CSS.
 *
 * Usage in templates:
 *   snippet('mosparo-field', ['id' => 'my-mosparo']);
 *   snippet('mosparo-field', ['id' => 'contact-mosparo', 'class' => 'my-widget']);
 *   snippet('mosparo-field', ['data' => ['theme' => 'dark']]);
 *
 * @var string $id Container element ID (default: mosparo-box)
 * @var string $class CSS class (default: mosparo-box)
 * @var array<string, string> $data Custom data-mosparo-* attributes
 */

use Uniform\Mosparo\Widget\WidgetRenderer;

// Set defaults
$id = $id ?? 'mosparo-box';
$class = $class ?? 'mosparo-box';
$data = $data ?? [];

echo WidgetRenderer::render([
    'id' => $id,
    'class' => $class,
    'data' => $data,
]);
