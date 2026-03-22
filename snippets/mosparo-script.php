<?php
/**
 * Mosparo Script Snippet
 *
 * Loads Mosparo frontend JavaScript and CSS.
 * Should be included once per page, typically in <head> or before </body>.
 *
 * Usage in templates:
 *   snippet('mosparo-script');
 *   snippet('mosparo-script', ['async' => true, 'defer' => true]);
 *   snippet('mosparo-script', ['async' => false]);
 *
 * The Mosparo frontend script automatically detects elements with the
 * 'mosparo-box' class and initializes the widget.
 *
 * @var bool $async Load script asynchronously (default: true)
 * @var bool $defer Defer script execution (default: true)
 */

use Uniform\Mosparo\Widget\WidgetRenderer;

// Set defaults
$async = $async ?? true;
$defer = $defer ?? true;

echo WidgetRenderer::renderScript([
    'async' => $async,
    'defer' => $defer,
]);
