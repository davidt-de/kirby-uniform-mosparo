<?php

/**
 * MosparoPlugin - Main Plugin Class
 *
 * Provides plugin registration logic for Kirby's plugin system.
 * Registers guards, options, translations, and snippets for Mosparo integration.
 *
 * @package Uniform\Mosparo
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo;

use Uniform\Mosparo\Guards\MosparoGuard;

/**
 * Main plugin class for Mosparo spam protection integration
 *
 * @package Uniform\Mosparo
 */
class MosparoPlugin
{
    /**
     * Plugin version
     */
    public const VERSION = '1.0.0';

    /**
     * Register plugin extensions with Kirby
     *
     * This method is called by Kirby::plugin() in index.php to register
     * all plugin extensions including guards, options, translations, and snippets.
     *
     * @return array<string, mixed> Plugin extension configuration
     * @throws \RuntimeException If required dependencies are missing
     */
    public static function register(): array
    {
        // Defensive check: ensure Uniform is installed
        if (!class_exists(\Uniform\Guards\Guard::class)) {
            throw new \RuntimeException(
                'Mosparo plugin requires mzur/kirby-uniform to be installed. ' .
                'Please run: composer require mzur/kirby-uniform'
            );
        }

        // Load helper functions
        require_once __DIR__ . '/helpers.php';

        return [
            // Register the Mosparo guard for form validation
            'guards' => [
                'mosparo' => MosparoGuard::class,
            ],

            // Default plugin options (expanded in Phase 2)
            'options' => [
                // Mosparo instance URL
                'host' => null,
                // Mosparo project public key
                'publicKey' => null,
                // Mosparo project private key
                'privateKey' => null,
                // UUID of the Mosparo ruleset to use
                'uuid' => null,
                // Enable debug mode
                'debug' => false,
                // Custom data-mosparo-* attributes for widget
                'dataAttributes' => [],
            ],

            // Translation files path (prepared for Phase 3)
            'translations' => [
                'en' => __DIR__ . '/../i18n/en.php',
                'de' => __DIR__ . '/../i18n/de.php',
            ],

            // Template snippets path (prepared for Phase 3)
            'snippets' => [
                'mosparo-script' => __DIR__ . '/../snippets/mosparo-script.php',
                'mosparo-field' => __DIR__ . '/../snippets/mosparo-field.php',
            ],
        ];
    }

    /**
     * Get plugin name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Mosparo';
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }
}
