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

// Load autoloader if classes are not already available (Composer autoloader should handle this in most cases)
if (!class_exists(Uniform\Mosparo\MosparoPlugin::class)) {
    $autoloadFile = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoloadFile)) {
        // When installed via Composer in a Kirby project, use Kirby's autoloader
        // The plugin is in vendor/davidt-de/kirby-uniform-mosparo, so go 3 levels up to project root
        $autoloadFile = __DIR__ . '/../../../vendor/autoload.php';
    }
    if (!file_exists($autoloadFile)) {
        // Alternative: maybe we're in site/plugins directly (not via symlink)
        $autoloadFile = __DIR__ . '/../../vendor/autoload.php';
    }
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
    }
}
require_once __DIR__ . '/src/helpers.php';

use Kirby\Cms\Kirby;
use Uniform\Mosparo\MosparoPlugin;

// Defensive coding: only register if we're in a Kirby context
if (!class_exists(Kirby::class)) {
    return;
}

// Register the plugin with Kirby
Kirby::plugin('davidt-de/uniform-mosparo', MosparoPlugin::register());
