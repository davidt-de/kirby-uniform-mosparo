<?php

/**
 * Kirby Uniform Mosparo Plugin - Entry Point
 *
 * This is the main entry point for the Kirby plugin. It loads the Composer
 * autoloader and registers the plugin with Kirby's plugin system.
 *
 * Plugin Name: getkirby-uniform/mosparo
 * Description: Mosparo spam protection for Kirby Uniform forms
 * Version: 1.0.0
 * Author: Patrick Schumacher
 * License: MIT
 *
 * @package Uniform\Mosparo
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/helpers.php';

use Kirby\Cms\Kirby;
use Uniform\Mosparo\MosparoPlugin;

// Defensive coding: only register if we're in a Kirby context
if (!class_exists(Kirby::class)) {
    return;
}

// Register the plugin with Kirby
Kirby::plugin('getkirby-uniform/mosparo', MosparoPlugin::register());
