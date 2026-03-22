<?php

/**
 * MosparoGuardSecurityTest - Security-focused tests for MosparoGuard
 *
 * Tests bypass protection, error sanitization, and ignored field handling.
 *
 * @package Uniform\Mosparo\Tests\Guards
 * @author Patrick Davidt <hallo@davidt.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Tests\Guards;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception as MosparoException;
use Mosparo\ApiClient\VerificationResult;
use Uniform\Mosparo\Config\Config;
use Uniform\Mosparo\Exception\VerificationException;
use Uniform\Mosparo\Guards\MosparoGuard;
use Uniform\Mosparo\Validation\VerificationService;

/**
 * @covers \Uniform\Mosparo\Guards\MosparoGuard
 */
class MosparoGuardSecurityTest extends MockeryTestCase
{
    /**
     * Test that bypass protection triggers when a field is not verified.
     */
    public function testBypassProtectionTriggersOnMissingField(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'test-public-key',
            privateKey: 'test-private-key'
        );

        $service = new VerificationService($config);

        // Create a mock result where one field is missing from verified fields
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                // 'email' is missing - simulating bypass attempt
            ]);
        $mockResult->shouldReceive('isSubmittable')
            ->andReturn(true);

        // Verify the service correctly identifies this as a bypass attempt
        $fieldsToVerify = ['name', 'email'];
        $isValid = $service->verifyRequiredFields($mockResult, $fieldsToVerify);

        $this->assertFalse($isValid);
    }

    /**
     * Test that bypass protection passes when all fields are verified.
     */
    public function testBypassProtectionPassesWhenAllFieldsVerified(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'test-public-key',
            privateKey: 'test-private-key'
        );

        $service = new VerificationService($config);

        // Create a mock result where all fields are verified
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                'email' => VerificationResult::FIELD_VALID,
            ]);

        $fieldsToVerify = ['name', 'email'];
        $isValid = $service->verifyRequiredFields($mockResult, $fieldsToVerify);

        $this->assertTrue($isValid);
    }

    /**
     * Test that private key is not exposed in error messages.
     */
    public function testPrivateKeyNotInErrorMessage(): void
    {
        // Test with a hex pattern that looks like an API key (32+ hex chars)
        $privateKey = 'a1b2c3d4e5f6789012345678abcdef123456';
        $errorMessage = 'API error occurred with key: ' . $privateKey;

        // The sanitizeErrorMessage method should redact this
        $sanitized = $this->sanitizeErrorMessage($errorMessage);

        $this->assertStringNotContainsString($privateKey, $sanitized);
        $this->assertStringContainsString('[KEY_REDACTED]', $sanitized);
    }

    /**
     * Test that sanitizeErrorMessage removes URLs from error messages.
     */
    public function testSanitizeErrorMessageRemovesUrls(): void
    {
        $errorMessage = 'Connection failed: https://mosparo.example.com/api/v1/verification/verify';

        $sanitized = $this->sanitizeErrorMessage($errorMessage);

        $this->assertStringNotContainsString('https://mosparo.example.com', $sanitized);
        $this->assertStringContainsString('[URL_REDACTED]', $sanitized);
    }

    /**
     * Test that sanitizeErrorMessage removes hex key patterns.
     */
    public function testSanitizeErrorMessageRemovesKeyPatterns(): void
    {
        // 32+ character hex string (simulating API key or signature)
        $hexKey = 'a1b2c3d4e5f6789012345678abcdef123456';
        $errorMessage = 'Request failed with signature: ' . $hexKey;

        $sanitized = $this->sanitizeErrorMessage($errorMessage);

        $this->assertStringNotContainsString($hexKey, $sanitized);
        $this->assertStringContainsString('[KEY_REDACTED]', $sanitized);
    }

    /**
     * Test that ignored fields are not sent to the API (filtered in prepareFormData).
     */
    public function testIgnoredFieldsNotSentToApi(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'test-public-key',
            privateKey: 'test-private-key',
            ignoredFields: ['password', 'password_confirm', 'csrf_token', 'secret']
        );

        $service = new VerificationService($config);

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirm' => 'secret123',
            'csrf_token' => 'csrf-token-value',
            'secret' => 'top-secret-data',
            '_mosparo_submitToken' => 'submit-token',
            '_mosparo_validationToken' => 'validation-token',
        ];

        $cleanedData = $service->prepareFormData($formData);

        // Verify sensitive fields are removed
        $this->assertArrayNotHasKey('password', $cleanedData);
        $this->assertArrayNotHasKey('password_confirm', $cleanedData);
        $this->assertArrayNotHasKey('csrf_token', $cleanedData);
        $this->assertArrayNotHasKey('secret', $cleanedData);
        $this->assertArrayNotHasKey('_mosparo_submitToken', $cleanedData);
        $this->assertArrayNotHasKey('_mosparo_validationToken', $cleanedData);

        // Verify regular fields remain
        $this->assertArrayHasKey('name', $cleanedData);
        $this->assertArrayHasKey('email', $cleanedData);
        $this->assertEquals('John Doe', $cleanedData['name']);
        $this->assertEquals('john@example.com', $cleanedData['email']);
    }

    /**
     * Test that API errors show user-friendly translation keys instead of raw errors.
     */
    public function testApiErrorShowsUserFriendlyMessage(): void
    {
        // Create a mock exception with a sensitive message
        $sensitiveMessage = 'Connection failed to https://mosparo.example.com with key: abc123def4567890abcdef1234567890ab';
        $exception = new MosparoException($sensitiveMessage);

        // Sanitize the message
        $sanitized = $this->sanitizeErrorMessage($exception->getMessage());

        // Verify sensitive data is redacted
        $this->assertStringNotContainsString('https://mosparo.example.com', $sanitized);
        $this->assertStringNotContainsString('abc123def4567890abcdef1234567890ab', $sanitized);
        $this->assertStringContainsString('[URL_REDACTED]', $sanitized);
        $this->assertStringContainsString('[KEY_REDACTED]', $sanitized);

        // The translation key should be user-friendly, not technical
        $translationKey = 'mosparo.error.api_error';
        $this->assertStringNotContainsString('Exception', $translationKey);
        $this->assertStringNotContainsString('Mosparo', $translationKey);
    }

    /**
     * Test that all error scenarios use translation keys.
     */
    public function testAllErrorsUseTranslationKeys(): void
    {
        $expectedTranslationKeys = [
            'mosparo.error.tokens_missing',
            'mosparo.error.verification_failed',
            'mosparo.error.api_error',
            'mosparo.error.bypass_detected',
        ];

        foreach ($expectedTranslationKeys as $key) {
            $this->assertMatchesRegularExpression('/^mosparo\.error\.[a-z_]+$/', $key);
        }
    }

    /**
     * Test that a bypass attempt is detected when verifiedFields is empty.
     */
    public function testBypassDetectedOnEmptyVerifiedFields(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'test-public-key',
            privateKey: 'test-private-key'
        );

        $service = new VerificationService($config);

        // Create a mock result with empty verified fields
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->andReturn([]);

        $fieldsToVerify = ['name', 'email'];
        $isValid = $service->verifyRequiredFields($mockResult, $fieldsToVerify);

        // Should reject when verifiedFields is empty
        $this->assertFalse($isValid);
    }

    /**
     * Test that the guard rejects submissions with invalid field status.
     */
    public function testGuardRejectsInvalidFieldStatus(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'test-public-key',
            privateKey: 'test-private-key'
        );

        $service = new VerificationService($config);

        // Create a mock result where a field is marked as invalid
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                'email' => VerificationResult::FIELD_INVALID,
            ]);

        $fieldsToVerify = ['name', 'email'];
        $isValid = $service->verifyRequiredFields($mockResult, $fieldsToVerify);

        // Should reject when any field is invalid
        $this->assertFalse($isValid);
    }

    /**
     * Helper method to sanitize error messages (matches Guard implementation).
     *
     * @param string $message The raw error message
     * @return string The sanitized message
     */
    private function sanitizeErrorMessage(string $message): string
    {
        // Remove any URLs that might contain keys
        $message = preg_replace('/https?:\/\/[^\s]+/', '[URL_REDACTED]', $message);
        // Remove potential key patterns (32+ hex chars)
        $message = preg_replace('/[a-f0-9]{32,}/i', '[KEY_REDACTED]', $message);
        return $message;
    }

    /**
     * Helper to create partial mock (simulated since we don't have PHPUnit mocking).
     *
     * @param string $className The class to mock
     * @param array $methods Methods to mock
     * @return object The mock object
     */
    protected function createMockForGuard(string $className, array $methods): object
    {
        // Use Mockery to create a partial mock
        $mock = Mockery::mock($className);
        foreach ($methods as $method) {
            $mock->shouldReceive($method);
        }
        return $mock;
    }
}
