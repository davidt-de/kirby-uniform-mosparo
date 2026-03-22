<?php

/**
 * ConfigFactory - Configuration Factory
 *
 * Factory for creating Config instances from Kirby options or direct arrays.
 * Handles option prefixing and default value application.
 *
 * @package Uniform\Mosparo\Config
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Config;

use Kirby\Cms\App;

/**
 * Factory for creating Config instances.
 *
 * Provides methods to create Config from Kirby options or directly from arrays.
 *
 * @package Uniform\Mosparo\Config
 */
final class ConfigFactory
{
    /**
     * Option prefix for all Mosparo configuration options.
     */
    private const OPTION_PREFIX = 'getkirby-uniform.mosparo.';

    /**
     * Create a Config instance from Kirby options.
     *
     * Reads configuration from Kirby's option() system with the prefix
     * 'getkirby-uniform.mosparo.'.
     *
     * @return Config
     */
    public static function fromKirbyOptions(): Config
    {
        $kirby = App::instance();

        return new Config(
            host: self::getStringOption($kirby, 'host'),
            uuid: self::getStringOption($kirby, 'uuid'),
            publicKey: self::getStringOption($kirby, 'publicKey'),
            privateKey: self::getStringOption($kirby, 'privateKey'),
            ignoredFields: self::getArrayOption($kirby, 'ignoredFields', Config::DEFAULT_IGNORED_FIELDS),
            cssUrl: self::getStringOption($kirby, 'cssUrl'),
            debug: self::getBoolOption($kirby, 'debug', false),
        );
    }

    /**
     * Create a Config instance directly from an options array.
     *
     * Useful for testing or when configuration comes from sources
     * other than Kirby options.
     *
     * @param array<string, mixed> $options Configuration options
     * @return Config
     */
    public static function create(array $options): Config
    {
        return new Config(
            host: self::getStringValue($options, 'host'),
            uuid: self::getStringValue($options, 'uuid'),
            publicKey: self::getStringValue($options, 'publicKey'),
            privateKey: self::getStringValue($options, 'privateKey'),
            ignoredFields: self::getArrayValue($options, 'ignoredFields', Config::DEFAULT_IGNORED_FIELDS),
            cssUrl: self::getStringValue($options, 'cssUrl'),
            debug: self::getBoolValue($options, 'debug', false),
        );
    }

    /**
     * Get a string option from Kirby.
     *
     * @param App $kirby Kirby application instance
     * @param string $key Option key (without prefix)
     * @return string|null
     */
    private static function getStringOption(App $kirby, string $key): ?string
    {
        $value = $kirby->option(self::OPTION_PREFIX . $key);

        if ($value === null) {
            return null;
        }

        // Ensure we return a string or null
        return is_string($value) ? $value : null;
    }

    /**
     * Get an array option from Kirby with default fallback.
     *
     * @param App $kirby Kirby application instance
     * @param string $key Option key (without prefix)
     * @param array<string> $default Default value if option not set
     * @return array<string>
     */
    private static function getArrayOption(App $kirby, string $key, array $default): array
    {
        $value = $kirby->option(self::OPTION_PREFIX . $key);

        if ($value === null) {
            return $default;
        }

        return is_array($value) ? $value : $default;
    }

    /**
     * Get a boolean option from Kirby with default fallback.
     *
     * @param App $kirby Kirby application instance
     * @param string $key Option key (without prefix)
     * @param bool $default Default value if option not set
     * @return bool
     */
    private static function getBoolOption(App $kirby, string $key, bool $default): bool
    {
        $value = $kirby->option(self::OPTION_PREFIX . $key);

        if ($value === null) {
            return $default;
        }

        return (bool) $value;
    }

    /**
     * Get a string value from an array.
     *
     * @param array<string, mixed> $array Source array
     * @param string $key Array key
     * @return string|null
     */
    private static function getStringValue(array $array, string $key): ?string
    {
        if (!isset($array[$key])) {
            return null;
        }

        $value = $array[$key];

        return is_string($value) ? $value : null;
    }

    /**
     * Get an array value from an array with default fallback.
     *
     * @param array<string, mixed> $array Source array
     * @param string $key Array key
     * @param array<string> $default Default value
     * @return array<string>
     */
    private static function getArrayValue(array $array, string $key, array $default): array
    {
        if (!isset($array[$key])) {
            return $default;
        }

        $value = $array[$key];

        return is_array($value) ? $value : $default;
    }

    /**
     * Get a boolean value from an array with default fallback.
     *
     * @param array<string, mixed> $array Source array
     * @param string $key Array key
     * @param bool $default Default value
     * @return bool
     */
    private static function getBoolValue(array $array, string $key, bool $default): bool
    {
        if (!isset($array[$key])) {
            return $default;
        }

        return (bool) $array[$key];
    }
}
