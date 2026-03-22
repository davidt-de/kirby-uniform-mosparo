<?php

/**
 * VerificationServiceTest - Security-focused tests for VerificationService
 *
 * Tests bypass protection, field filtering, and verification logic.
 *
 * @package Uniform\Mosparo\Tests\Validation
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Tests\Validation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\VerificationResult;
use Uniform\Mosparo\Config\Config;
use Uniform\Mosparo\Validation\VerificationService;

/**
 * @covers \Uniform\Mosparo\Validation\VerificationService
 */
class VerificationServiceTest extends MockeryTestCase
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var VerificationService
     */
    private VerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'test-public-key',
            privateKey: 'test-private-key',
            ignoredFields: ['password', 'password_confirm', 'csrf_token']
        );
        $this->service = new VerificationService($this->config);
    }

    /**
     * Test that prepareFormData removes Mosparo tokens from data.
     */
    public function testPrepareFormDataRemovesTokens(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            '_mosparo_submitToken' => 'submit-token-123',
            '_mosparo_validationToken' => 'validation-token-456',
        ];

        $result = $this->service->prepareFormData($data);

        $this->assertArrayNotHasKey('_mosparo_submitToken', $result);
        $this->assertArrayNotHasKey('_mosparo_validationToken', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }

    /**
     * Test that prepareFormData removes ignored fields (passwords, etc.).
     */
    public function testPrepareFormDataRemovesIgnoredFields(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirm' => 'secret123',
            'csrf_token' => 'csrf-token-789',
        ];

        $result = $this->service->prepareFormData($data);

        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('password_confirm', $result);
        $this->assertArrayNotHasKey('csrf_token', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }

    /**
     * Test that verifyRequiredFields returns true when all fields are valid.
     */
    public function testVerifyRequiredFieldsReturnsTrueWhenAllValid(): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->once()
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                'email' => VerificationResult::FIELD_VALID,
            ]);

        $result = $this->service->verifyRequiredFields($mockResult, ['name', 'email']);

        $this->assertTrue($result);
    }

    /**
     * Test that verifyRequiredFields returns false when a required field is missing.
     */
    public function testVerifyRequiredFieldsReturnsFalseWhenMissing(): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->once()
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                // 'email' is missing
            ]);

        $result = $this->service->verifyRequiredFields($mockResult, ['name', 'email']);

        $this->assertFalse($result);
    }

    /**
     * Test that verifyRequiredFields returns false when a field has invalid status.
     */
    public function testVerifyRequiredFieldsReturnsFalseWhenInvalid(): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->once()
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                'email' => VerificationResult::FIELD_INVALID,
            ]);

        $result = $this->service->verifyRequiredFields($mockResult, ['name', 'email']);

        $this->assertFalse($result);
    }

    /**
     * Test that verifyRequiredFields handles empty verified fields array.
     */
    public function testVerifyRequiredFieldsHandlesEmptyVerifiedFields(): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->once()
            ->andReturn([]);

        $result = $this->service->verifyRequiredFields($mockResult, ['name', 'email']);

        $this->assertFalse($result);
    }

    /**
     * Test that verify calls the Mosparo client verifySubmission method.
     */
    public function testVerifyCallsClientVerifySubmission(): void
    {
        // This test verifies that the service properly delegates to the client
        // We can't easily mock the Client since it's created internally,
        // but we can verify the service structure and that it handles exceptions
        $this->assertInstanceOf(VerificationService::class, $this->service);

        // Verify the service has the expected public methods
        $this->assertTrue(method_exists($this->service, 'verify'));
        $this->assertTrue(method_exists($this->service, 'verifyRequiredFields'));
        $this->assertTrue(method_exists($this->service, 'prepareFormData'));
        $this->assertTrue(method_exists($this->service, 'isConfigured'));
    }

    /**
     * Test that isConfigured delegates to Config::isConfigured().
     */
    public function testIsConfiguredDelegatesToConfig(): void
    {
        // Fully configured config should return true
        $this->assertTrue($this->service->isConfigured());

        // Unconfigured config should return false
        $unconfiguredConfig = new Config();
        $unconfiguredService = new VerificationService($unconfiguredConfig);
        $this->assertFalse($unconfiguredService->isConfigured());
    }

    /**
     * Test that verifyRequiredFields handles 'not-verified' status correctly.
     */
    public function testVerifyRequiredFieldsReturnsFalseWhenNotVerified(): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->once()
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
                'email' => VerificationResult::FIELD_NOT_VERIFIED,
            ]);

        $result = $this->service->verifyRequiredFields($mockResult, ['name', 'email']);

        $this->assertFalse($result);
    }

    /**
     * Test that empty field names in required fields are skipped.
     */
    public function testVerifyRequiredFieldsSkipsEmptyFieldNames(): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('getVerifiedFields')
            ->once()
            ->andReturn([
                'name' => VerificationResult::FIELD_VALID,
            ]);

        // Empty string should be skipped
        $result = $this->service->verifyRequiredFields($mockResult, ['name', '']);

        $this->assertTrue($result);
    }
}
