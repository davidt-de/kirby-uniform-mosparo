<?php

/**
 * Kirby Uniform Mosparo Plugin - Entry Point
 *
 * This is the main entry point for the Kirby plugin. It loads the Composer
 * autoloader and registers the plugin with Kirby's plugin system.
 *
 * Plugin Name: davidt-de/uniform-mosparo
 * Description: Mosparo spam protection for Kirby Uniform forms
 * Version: 1.0.0
 * Author: Patrick Davidt
 * License: MIT
 *
 * @package Uniform\Mosparo
 * @author Patrick Davidt <hallo@davidt.de>
 * @license MIT
 */

// Load autoloader - check for plugin's own vendor first (standalone), then fall back to Kirby's vendor
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    // When installed via Composer in a Kirby project, use Kirby's autoloader
    $autoloadFile = __DIR__ . '/../../vendor/autoload.php';
}
require_once $autoloadFile;
require_once __DIR__ . '/src/helpers.php';

use Kirby\Cms\Kirby;
use Uniform\Mosparo\MosparoPlugin;

// Defensive coding: only register if we're in a Kirby context
if (!class_exists(Kirby::class)) {
    return;
}

// Register the plugin with Kirby
Kirby::plugin('davidt-de/uniform-mosparo', MosparoPlugin::register());
