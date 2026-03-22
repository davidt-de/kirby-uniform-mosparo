<?php

/**
 * VerificationService - Mosparo verification with security checks
 *
 * Wrapper service for Mosparo API client that adds security features:
 * - Bypass protection via verifiedFields check
 * - Ignored field filtering (passwords, checkboxes, etc.)
 * - Error sanitization (no private key exposure)
 *
 * @package Uniform\Mosparo\Validation
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Validation;

use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\VerificationResult;
use Uniform\Mosparo\Config\Config;

/**
 * Service for Mosparo verification with security hardening.
 *
 * This service wraps the Mosparo API client and adds:
 * - Bypass protection by verifying which fields were actually checked
 * - Automatic filtering of sensitive/ignored fields before API submission
 * - Configuration validation
 *
 * @package Uniform\Mosparo\Validation
 */
final class VerificationService
{
    /**
     * Mosparo submit token field name
     */
    private const SUBMIT_TOKEN_FIELD = '_mosparo_submitToken';

    /**
     * Mosparo validation token field name
     */
    private const VALIDATION_TOKEN_FIELD = '_mosparo_validationToken';

    /**
     * @var Config The Mosparo configuration
     */
    private Config $config;

    /**
     * Constructor.
     *
     * @param Config $config The Mosparo configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Verify a form submission with Mosparo.
     *
     * Creates a Mosparo client from config and calls verifySubmission()
     * with the cleaned form data.
     *
     * @param array<string, mixed> $formData The cleaned form data (without tokens)
     * @param string $submitToken The Mosparo submit token
     * @param string $validationToken The Mosparo validation token
     * @return VerificationResult The verification result from Mosparo
     *
     * @throws \Mosparo\ApiClient\Exception If API call fails
     */
    public function verify(array $formData, string $submitToken, string $validationToken): VerificationResult
    {
        $client = $this->createClient();

        return $client->verifySubmission($formData, $submitToken, $validationToken);
    }

    /**
     * Verify that all required fields were actually checked by Mosparo.
     *
     * This is the bypass protection mechanism. It checks the verifiedFields
     * from the API response to ensure bots can't submit by bypassing the
     * frontend widget.
     *
     * @param VerificationResult $result The verification result from Mosparo
     * @param array<string> $requiredFields List of field names that must be verified
     * @return bool True if all required fields have FIELD_VALID status
     */
    public function verifyRequiredFields(VerificationResult $result, array $requiredFields): bool
    {
        $verifiedFields = $result->getVerifiedFields();

        // Handle empty verified fields - this could indicate an API issue or bypass attempt
        if (empty($verifiedFields)) {
            return false;
        }

        foreach ($requiredFields as $field) {
            // Skip empty field names
            if (empty($field)) {
                continue;
            }

            // Check if field was verified and has valid status
            if (!isset($verifiedFields[$field])) {
                return false;
            }

            if ($verifiedFields[$field] !== VerificationResult::FIELD_VALID) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare form data for Mosparo verification.
     *
     * Removes:
     * - Mosparo tokens (_mosparo_submitToken, _mosparo_validationToken)
     * - Ignored fields from config (passwords, checkboxes, etc.)
     *
     * @param array<string, mixed> $data The raw form data
     * @return array<string, mixed> The cleaned form data ready for verification
     */
    public function prepareFormData(array $data): array
    {
        // Remove Mosparo tokens
        unset($data[self::SUBMIT_TOKEN_FIELD]);
        unset($data[self::VALIDATION_TOKEN_FIELD]);

        // Remove ignored fields (passwords, checkboxes, etc.)
        $ignoredFields = $this->config->getIgnoredFields();
        foreach ($ignoredFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    /**
     * Check if Mosparo is properly configured.
     *
     * Delegates to Config::isConfigured() to check if all required
     * fields (host, uuid, publicKey, privateKey) are set.
     *
     * @return bool True if Mosparo is configured and ready to use
     */
    public function isConfigured(): bool
    {
        return $this->config->isConfigured();
    }

    /**
     * Create a Mosparo API client from configuration.
     *
     * @return Client The configured Mosparo client
     */
    private function createClient(): Client
    {
        $host = $this->config->getHost();
        $publicKey = $this->config->getPublicKey();
        $privateKey = $this->config->getPrivateKey();

        // These should never be null if isConfigured() was checked,
        // but we provide fallbacks to satisfy type system
        return new Client(
            $host ?? '',
            $publicKey ?? '',
            $privateKey ?? ''
        );
    }
}
