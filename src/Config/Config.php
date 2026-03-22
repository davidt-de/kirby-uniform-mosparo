<?php

/**
 * Config - Configuration Value Object
 *
 * Immutable configuration value object for Mosparo integration.
 * Provides type-safe access to all configuration values with validation.
 *
 * Security note: The private key is stored internally but should never
 * be exposed in frontend contexts or logged.
 *
 * @package Uniform\Mosparo\Config
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Config;

/**
 * Configuration value object for Mosparo spam protection.
 *
 * This class holds all configuration values needed for Mosparo integration.
 * It is immutable - once created, values cannot be changed.
 *
 * @package Uniform\Mosparo\Config
 */
final class Config
{
    /**
     * Default ignored fields that won't be sent to Mosparo
     * @var array<string>
     */
    public const DEFAULT_IGNORED_FIELDS = ['password', 'password_confirm', 'csrf_token'];

    /**
     * @param string|null $host Mosparo instance URL
     * @param string|null $uuid Mosparo project UUID
     * @param string|null $publicKey Mosparo public key (frontend-safe)
     * @param string|null $privateKey Mosparo private key (server-side only)
     * @param array<string> $ignoredFields Form fields to exclude from verification
     * @param string|null $cssUrl Optional custom CSS URL for widget styling
     * @param bool $debug Enable debug mode for troubleshooting
     */
    public function __construct(
        private readonly ?string $host = null,
        private readonly ?string $uuid = null,
        private readonly ?string $publicKey = null,
        private readonly ?string $privateKey = null,
        private readonly array $ignoredFields = self::DEFAULT_IGNORED_FIELDS,
        private readonly ?string $cssUrl = null,
        private readonly bool $debug = false,
    ) {
    }

    /**
     * Get the Mosparo host URL.
     *
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Get the Mosparo project UUID.
     *
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * Get the Mosparo public key.
     *
     * This key is safe to expose in frontend contexts.
     *
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Get the Mosparo private key.
     *
     * WARNING: This key should NEVER be exposed in frontend code,
     * HTML output, logs, or error messages. Use only for server-side
     * API verification.
     *
     * @return string|null
     */
    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    /**
     * Get the list of ignored form fields.
     *
     * These fields will be excluded from Mosparo verification.
     *
     * @return array<string>
     */
    public function getIgnoredFields(): array
    {
        return $this->ignoredFields;
    }

    /**
     * Get the custom CSS URL for widget styling.
     *
     * @return string|null
     */
    public function getCssUrl(): ?string
    {
        return $this->cssUrl;
    }

    /**
     * Check if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Check if all required configuration fields are set.
     *
     * Required fields: host, uuid, publicKey, privateKey
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->host !== null
            && $this->host !== ''
            && $this->uuid !== null
            && $this->uuid !== ''
            && $this->publicKey !== null
            && $this->publicKey !== ''
            && $this->privateKey !== null
            && $this->privateKey !== '';
    }
}
