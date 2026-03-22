<?php
/**
 * Mosparo Initialization Snippet
 *
 * Renders the JavaScript code to initialize the Mosparo widget.
 * This is required for the widget to work - Mosparo does not auto-detect
 * data attributes on the container element.
 *
 * Must be included AFTER:
 * 1. The mosparo-field snippet (widget container)
 * 2. The mosparo-script snippet (frontend JavaScript)
 *
 * Usage in templates:
 *   snippet('mosparo-init');
 *   snippet('mosparo-init', ['id' => 'my-mosparo']);
 *   snippet('mosparo-init', ['loadCssResource' => false]);
 *   snippet('mosparo-init', ['customOptions' => ['language' => 'de']]);
 *
 * @var string $id HTML id of the widget container (default: mosparo-box)
 * @var bool $loadCssResource Whether to load CSS automatically (default: true)
 * @var array<string, mixed> $customOptions Additional JavaScript options
 *
 * @see https://documentation.mosparo.io/de/docs/integration/custom
 */

use Uniform\Mosparo\Widget\WidgetRenderer;

// Set defaults
$id = $id ?? 'mosparo-box';
$loadCssResource = $loadCssResource ?? true;
$customOptions = $customOptions ?? [];

echo WidgetRenderer::renderInitScript([
    'id' => $id,
    'loadCssResource' => $loadCssResource,
    'customOptions' => $customOptions,
]);
