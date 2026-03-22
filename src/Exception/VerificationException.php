<?php

/**
 * VerificationException - Custom exception for Mosparo verification failures
 *
 * Provides translation key support for i18n error messages.
 *
 * @package Uniform\Mosparo\Exception
 * @author Patrick Davidt <hallo@davidt.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Exception;

use Exception;
use Throwable;

/**
 * Custom exception for Mosparo verification failures with translation key support.
 *
 * This exception allows the guard to specify a translation key that can be
 * used to display localized error messages to users.
 *
 * @package Uniform\Mosparo\Exception
 */
class VerificationException extends Exception
{
    /**
     * Translation key for not configured error
     */
    public const NOT_CONFIGURED = 'mosparo.error.not_configured';

    /**
     * Translation key for verification failed error
     */
    public const VERIFICATION_FAILED = 'mosparo.error.verification_failed';

    /**
     * Translation key for API error
     */
    public const API_ERROR = 'mosparo.error.api_error';

    /**
     * Translation key for tokens missing error
     */
    public const TOKENS_MISSING = 'mosparo.error.tokens_missing';

    /**
     * @var string The translation key for i18n
     */
    private string $translationKey;

    /**
     * Constructor.
     *
     * @param string $translationKey The translation key for i18n
     * @param string $message The exception message (for logging)
     * @param int $code The exception code
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(
        string $translationKey,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->translationKey = $translationKey;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the translation key for i18n.
     *
     * @return string
     */
    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }
}
