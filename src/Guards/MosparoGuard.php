<?php

/**
 * MosparoGuard - Spam Protection Guard
 *
 * Implements spam protection verification using Mosparo.io service.
 * Validates form submissions against Mosparo's spam detection API.
 *
 * @see https://mosparo.io/docs for API reference
 * @package Uniform\Mosparo\Guards
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 * @extends \Uniform\Guards\Guard
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Guards;

use Kirby\Cms\App;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception as MosparoException;
use Uniform\Guards\Guard;
use Uniform\Mosparo\Config\Config;
use Uniform\Mosparo\Config\ConfigFactory;
use Uniform\Mosparo\Exception\VerificationException;

/**
 * Mosparo spam protection Guard for Kirby Uniform.
 *
 * Validates form submissions against Mosparo's spam detection API.
 * Supports both checkbox and invisible verification modes.
 *
 * @package Uniform\Mosparo\Guards
 */
class MosparoGuard extends Guard
{
    /**
     * Perform the verification.
     *
     * Validates the submission token from Mosparo client-side widget
     * against the Mosparo API. Calls reject() on validation failure.
     *
     * @throws VerificationException If Mosparo is not configured
     */
    public function perform(): void
    {
        // Load configuration
        $config = ConfigFactory::fromKirbyOptions();

        // Check if configured
        if (!$config->isConfigured()) {
            throw new VerificationException(
                VerificationException::NOT_CONFIGURED,
                'Mosparo is not configured. Please set host, uuid, publicKey, and privateKey options.'
            );
        }

        // Extract Mosparo tokens from request body
        $requestBody = App::instance()->request()->body();
        $submitToken = $requestBody->get('_mosparo_submitToken');
        $validationToken = $requestBody->get('_mosparo_validationToken');

        if (empty($submitToken) || empty($validationToken)) {
            $this->reject(
                VerificationException::TOKENS_MISSING,
                'mosparo'
            );
            return;
        }

        // Create Mosparo API client
        $client = new Client(
            $config->getHost(),
            $config->getPublicKey(),
            $config->getPrivateKey()
        );

        // Prepare form data (remove Mosparo tokens and ignored fields)
        $formData = $this->prepareFormData($requestBody->toArray(), $config);

        try {
            // Verify submission
            $result = $client->verifySubmission($formData, $submitToken, $validationToken);
        } catch (MosparoException $e) {
            // Log error (without private key details)
            error_log('Mosparo API error: ' . $e->getMessage());

            $this->reject(
                VerificationException::API_ERROR,
                'mosparo'
            );
            return;
        }

        // Check if submission is valid
        if (!$result->isSubmittable()) {
            $this->reject(
                VerificationException::VERIFICATION_FAILED,
                'mosparo'
            );
            return;
        }

        // Success - do nothing (Guard passed)
    }

    /**
     * Prepare form data for Mosparo verification.
     *
     * Removes Mosparo tokens and any ignored fields before sending to API.
     *
     * @param array<string, mixed> $data Raw form data
     * @param Config $config Configuration with ignored fields
     * @return array<string, mixed> Cleaned form data
     */
    private function prepareFormData(array $data, Config $config): array
    {
        // Remove Mosparo tokens
        unset($data['_mosparo_submitToken'], $data['_mosparo_validationToken']);

        // Remove ignored fields
        foreach ($config->getIgnoredFields() as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
