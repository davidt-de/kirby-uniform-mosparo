<?php

/**
 * Example configuration for Mosparo plugin
 *
 * Copy this file to your Kirby site's config folder and customize the values.
 * Typically placed at: site/config/config.php
 *
 * @see https://mosparo.io/docs for Mosparo setup instructions
 */

return [
    // =========================================================================
    // Mosparo Configuration
    // =========================================================================
    
    /**
     * Mosparo instance URL
     * The URL where your Mosparo instance is hosted
     */
    'getkirby-uniform.mosparo.host' => 'https://mosparo.example.com',
    
    /**
     * Mosparo project UUID
     * Found in your Mosparo project settings
     */
    'getkirby-uniform.mosparo.uuid' => 'your-project-uuid-here',
    
    /**
     * Mosparo public key
     * Used for frontend widget initialization (safe to expose)
     */
    'getkirby-uniform.mosparo.publicKey' => 'your-public-key-here',
    
    /**
     * Mosparo private key
     * Used for server-side API verification (keep secret!)
     */
    'getkirby-uniform.mosparo.privateKey' => 'your-private-key-here',
    
    /**
     * Fields to ignore during verification
     * These fields won't be sent to Mosparo (e.g., passwords, CSRF tokens)
     * Default: ['password', 'password_confirm', 'csrf_token']
     */
    'getkirby-uniform.mosparo.ignoredFields' => [
        'password',
        'password_confirm',
        'csrf_token',
    ],
    
    /**
     * Custom CSS URL for widget styling
     * Optional: URL to custom CSS file for Mosparo widget appearance
     */
    'getkirby-uniform.mosparo.cssUrl' => null,
    
    /**
     * Debug mode
     * Enable detailed logging for troubleshooting
     * Default: false
     */
    'getkirby-uniform.mosparo.debug' => false,
];
