<?php

/**
 * MosparoGuardTest - Guard Verification Tests
 *
 * Comprehensive test suite for MosparoGuard with mocked Mosparo API client.
 *
 * @package Uniform\Mosparo\Tests
 * @author Patrick Schumacher <hello@patrick-schumacher.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Tests;

use Kirby\Cms\App;
use Kirby\Http\Request\Body;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception as MosparoException;
use Mosparo\ApiClient\VerificationResult;
use Uniform\Exceptions\PerformerException;
use Uniform\Form;
use Uniform\Mosparo\Exception\VerificationException;
use Uniform\Mosparo\Guards\MosparoGuard;

/**
 * Simple Form stub for testing.
 */
class FormStub extends Form
{
    /**
     * @param array $rules
     * @param string|null $sessionKey
     */
    public function __construct($rules = [], $sessionKey = null)
    {
        // Skip parent constructor - we don't need session/flash for tests
    }
}

/**
 * Test suite for MosparoGuard verification logic.
 *
 * @covers \Uniform\Mosparo\Guards\MosparoGuard
 */
final class MosparoGuardTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Create a mock Kirby app with configuration and request data.
     *
     * @param array<string, mixed> $config Optional config overrides
     * @param array<string, mixed> $requestBody Request body data
     * @return \Mockery\MockInterface
     */
    private function mockKirbyApp(array $config = [], array $requestBody = [])
    {
        $defaultConfig = [
            'getkirby-uniform.mosparo.host' => 'https://mosparo.example.com',
            'getkirby-uniform.mosparo.uuid' => 'test-uuid',
            'getkirby-uniform.mosparo.publicKey' => 'test-public-key',
            'getkirby-uniform.mosparo.privateKey' => 'test-private-key',
            'getkirby-uniform.mosparo.ignoredFields' => ['password', 'password_confirm', 'csrf_token'],
        ];

        $config = array_merge($defaultConfig, $config);

        // Mock the request body - use get() for individual fields
        $mockBody = Mockery::mock(Body::class);
        $mockBody->shouldReceive('get')->andReturnUsing(function ($key) use ($requestBody) {
            return $requestBody[$key] ?? null;
        });
        $mockBody->shouldReceive('toArray')->andReturn($requestBody);

        // Mock the request
        $mockRequest = Mockery::mock('Kirby\Http\Request');
        $mockRequest->shouldReceive('body')->andReturn($mockBody);

        // Mock the App
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);
        $mockApp->shouldReceive('request')->andReturn($mockRequest);
        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) use ($config) {
                return $config[$key] ?? null;
            });

        return $mockApp;
    }

    /**
     * Create a mock Mosparo client that returns a specific result.
     *
     * @param bool $isSubmittable Whether the submission should be valid
     * @param string|null $exceptionClass Exception to throw, if any
     */
    private function mockMosparoClient(bool $isSubmittable, ?string $exceptionClass = null): void
    {
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('isSubmittable')->andReturn($isSubmittable);

        $mockClient = Mockery::mock('overload:' . Client::class);

        if ($exceptionClass !== null) {
            $mockClient->shouldReceive('verifySubmission')
                ->andThrow(new $exceptionClass('API error'));
        } else {
            $mockClient->shouldReceive('verifySubmission')->andReturn($mockResult);
        }
    }

    /**
     * Create a guard with rejection tracking.
     *
     * @return MosparoGuard
     */
    private function createGuardWithRejectionTracking(): MosparoGuard
    {
        // Create a partial mock that overrides reject()
        $guard = Mockery::mock(MosparoGuard::class . '[reject]', [new FormStub()])->makePartial();

        // Override reject to throw PerformerException
        $guard->shouldAllowMockingProtectedMethods();
        $guard->shouldReceive('reject')->andReturnUsing(function ($message, $key = null) {
            throw new PerformerException($message, $key);
        });

        return $guard;
    }

    // =========================================================================
    // Configuration Tests
    // =========================================================================

    /**
     * @test
     */
    public function testPerformThrowsExceptionWhenNotConfigured(): void
    {
        $this->mockKirbyApp([
            'getkirby-uniform.mosparo.host' => null,
            'getkirby-uniform.mosparo.uuid' => null,
            'getkirby-uniform.mosparo.publicKey' => null,
            'getkirby-uniform.mosparo.privateKey' => null,
        ], []);

        $guard = new MosparoGuard(new FormStub());

        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('Mosparo is not configured');

        $guard->perform();
    }

    /**
     * @test
     */
    public function testPerformThrowsExceptionWhenPartiallyConfigured(): void
    {
        $this->mockKirbyApp([
            'getkirby-uniform.mosparo.host' => 'https://mosparo.example.com',
            'getkirby-uniform.mosparo.uuid' => null, // Missing
            'getkirby-uniform.mosparo.publicKey' => 'key',
            'getkirby-uniform.mosparo.privateKey' => 'key',
        ], []);

        $guard = new MosparoGuard(new FormStub());

        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('Mosparo is not configured');

        $guard->perform();
    }

    // =========================================================================
    // Token Extraction Tests
    // =========================================================================

    /**
     * @test
     */
    public function testPerformRejectsWhenTokensMissing(): void
    {
        $this->mockKirbyApp([], []);

        $guard = $this->createGuardWithRejectionTracking();

        try {
            $guard->perform();
            $this->fail('Expected PerformerException was not thrown');
        } catch (PerformerException $e) {
            $this->assertEquals(VerificationException::TOKENS_MISSING, $e->getMessage());
            $this->assertEquals('mosparo', $e->getKey());
        }
    }

    /**
     * @test
     */
    public function testPerformRejectsWhenSubmitTokenMissing(): void
    {
        $this->mockKirbyApp([], ['_mosparo_validationToken' => 'validation-token-123']);

        $guard = $this->createGuardWithRejectionTracking();

        try {
            $guard->perform();
            $this->fail('Expected PerformerException was not thrown');
        } catch (PerformerException $e) {
            $this->assertEquals(VerificationException::TOKENS_MISSING, $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function testPerformRejectsWhenValidationTokenMissing(): void
    {
        $this->mockKirbyApp([], ['_mosparo_submitToken' => 'submit-token-123']);

        $guard = $this->createGuardWithRejectionTracking();

        try {
            $guard->perform();
            $this->fail('Expected PerformerException was not thrown');
        } catch (PerformerException $e) {
            $this->assertEquals(VerificationException::TOKENS_MISSING, $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function testPerformRejectsWhenTokensEmpty(): void
    {
        $this->mockKirbyApp([], [
            '_mosparo_submitToken' => '',
            '_mosparo_validationToken' => '',
        ]);

        $guard = $this->createGuardWithRejectionTracking();

        try {
            $guard->perform();
            $this->fail('Expected PerformerException was not thrown');
        } catch (PerformerException $e) {
            $this->assertEquals(VerificationException::TOKENS_MISSING, $e->getMessage());
        }
    }

    // =========================================================================
    // API Verification Tests
    // =========================================================================

    /**
     * @test
     */
    public function testPerformCallsVerifySubmissionWithCorrectData(): void
    {
        $submitToken = 'submit-token-abc';
        $validationToken = 'validation-token-xyz';
        $requestBody = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            '_mosparo_submitToken' => $submitToken,
            '_mosparo_validationToken' => $validationToken,
        ];

        $this->mockKirbyApp([], $requestBody);

        // Mock the client and verify it receives the correct data
        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('isSubmittable')->andReturn(true);

        $capturedData = null;
        $capturedSubmitToken = null;
        $capturedValidationToken = null;

        $mockClient = Mockery::mock('overload:' . Client::class);
        $mockClient->shouldReceive('verifySubmission')
            ->once()
            ->with(
                Mockery::on(function ($data) {
                    // Tokens should be removed from form data
                    return !isset($data['_mosparo_submitToken'])
                        && !isset($data['_mosparo_validationToken'])
                        && $data['name'] === 'John Doe'
                        && $data['email'] === 'john@example.com';
                }),
                Mockery::on(function ($token) use (&$capturedSubmitToken) {
                    $capturedSubmitToken = $token;
                    return true;
                }),
                Mockery::on(function ($token) use (&$capturedValidationToken) {
                    $capturedValidationToken = $token;
                    return true;
                })
            )
            ->andReturn($mockResult);

        $guard = new MosparoGuard(new FormStub());
        $guard->perform();

        // Verify tokens were passed correctly
        $this->assertEquals($submitToken, $capturedSubmitToken);
        $this->assertEquals($validationToken, $capturedValidationToken);
    }

    /**
     * @test
     */
    public function testPerformPassesWhenValid(): void
    {
        $this->mockKirbyApp([], [
            'name' => 'John',
            '_mosparo_submitToken' => 'submit-123',
            '_mosparo_validationToken' => 'validation-456',
        ]);
        $this->mockMosparoClient(true);

        $guard = new MosparoGuard(new FormStub());

        // Should not throw exception
        $guard->perform();

        // If we get here, the test passed
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function testPerformRejectsWhenInvalid(): void
    {
        $this->mockKirbyApp([], [
            'name' => 'John',
            '_mosparo_submitToken' => 'submit-123',
            '_mosparo_validationToken' => 'validation-456',
        ]);
        $this->mockMosparoClient(false);

        $guard = $this->createGuardWithRejectionTracking();

        try {
            $guard->perform();
            $this->fail('Expected PerformerException was not thrown');
        } catch (PerformerException $e) {
            $this->assertEquals(VerificationException::VERIFICATION_FAILED, $e->getMessage());
            $this->assertEquals('mosparo', $e->getKey());
        }
    }

    /**
     * @test
     */
    public function testPerformHandlesApiException(): void
    {
        $this->mockKirbyApp([], [
            'name' => 'John',
            '_mosparo_submitToken' => 'submit-123',
            '_mosparo_validationToken' => 'validation-456',
        ]);
        $this->mockMosparoClient(true, MosparoException::class);

        $guard = $this->createGuardWithRejectionTracking();

        try {
            $guard->perform();
            $this->fail('Expected PerformerException was not thrown');
        } catch (PerformerException $e) {
            $this->assertEquals(VerificationException::API_ERROR, $e->getMessage());
            $this->assertEquals('mosparo', $e->getKey());
        }
    }

    // =========================================================================
    // Data Handling Tests
    // =========================================================================

    /**
     * @test
     */
    public function testTokensAreRemovedFromFormData(): void
    {
        $submitToken = 'submit-abc';
        $validationToken = 'validation-xyz';
        $requestBody = [
            'field1' => 'value1',
            'field2' => 'value2',
            '_mosparo_submitToken' => $submitToken,
            '_mosparo_validationToken' => $validationToken,
        ];

        $this->mockKirbyApp([], $requestBody);

        $capturedData = null;

        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('isSubmittable')->andReturn(true);

        $mockClient = Mockery::mock('overload:' . Client::class);
        $mockClient->shouldReceive('verifySubmission')
            ->andReturnUsing(function ($data) use (&$capturedData, $mockResult) {
                $capturedData = $data;
                return $mockResult;
            });

        $guard = new MosparoGuard(new FormStub());
        $guard->perform();

        $this->assertArrayNotHasKey('_mosparo_submitToken', $capturedData);
        $this->assertArrayNotHasKey('_mosparo_validationToken', $capturedData);
        $this->assertEquals('value1', $capturedData['field1']);
        $this->assertEquals('value2', $capturedData['field2']);
    }

    /**
     * @test
     */
    public function testIgnoredFieldsAreRemovedFromFormData(): void
    {
        $this->mockKirbyApp(
            ['getkirby-uniform.mosparo.ignoredFields' => ['password', 'secret']],
            [
                'name' => 'John',
                'password' => 'secret123',
                'secret' => 'hidden-value',
                '_mosparo_submitToken' => 'submit-123',
                '_mosparo_validationToken' => 'validation-456',
            ]
        );

        $capturedData = null;

        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('isSubmittable')->andReturn(true);

        $mockClient = Mockery::mock('overload:' . Client::class);
        $mockClient->shouldReceive('verifySubmission')
            ->andReturnUsing(function ($data) use (&$capturedData, $mockResult) {
                $capturedData = $data;
                return $mockResult;
            });

        $guard = new MosparoGuard(new FormStub());
        $guard->perform();

        $this->assertArrayHasKey('name', $capturedData);
        $this->assertArrayNotHasKey('password', $capturedData);
        $this->assertArrayNotHasKey('secret', $capturedData);
    }

    /**
     * @test
     */
    public function testCsrfTokenIsRemovedByDefault(): void
    {
        $this->mockKirbyApp([], [
            'name' => 'John',
            'csrf_token' => 'csrf-value-123',
            '_mosparo_submitToken' => 'submit-123',
            '_mosparo_validationToken' => 'validation-456',
        ]);

        $capturedData = null;

        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('isSubmittable')->andReturn(true);

        $mockClient = Mockery::mock('overload:' . Client::class);
        $mockClient->shouldReceive('verifySubmission')
            ->andReturnUsing(function ($data) use (&$capturedData, $mockResult) {
                $capturedData = $data;
                return $mockResult;
            });

        $guard = new MosparoGuard(new FormStub());
        $guard->perform();

        $this->assertArrayHasKey('name', $capturedData);
        $this->assertArrayNotHasKey('csrf_token', $capturedData);
    }

    /**
     * @test
     */
    public function testEmptyFormDataWorks(): void
    {
        $this->mockKirbyApp([], [
            '_mosparo_submitToken' => 'submit-123',
            '_mosparo_validationToken' => 'validation-456',
        ]);
        $this->mockMosparoClient(true);

        $guard = new MosparoGuard(new FormStub());
        $guard->perform();

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function testComplexFormDataHandledCorrectly(): void
    {
        $this->mockKirbyApp([], [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message with special chars: äöüß €100',
            'password' => 'secret123',
            'password_confirm' => 'secret123',
            'csrf_token' => 'csrf-value',
            '_mosparo_submitToken' => 'submit-abc',
            '_mosparo_validationToken' => 'validation-xyz',
        ]);

        $capturedData = null;

        $mockResult = Mockery::mock(VerificationResult::class);
        $mockResult->shouldReceive('isSubmittable')->andReturn(true);

        $mockClient = Mockery::mock('overload:' . Client::class);
        $mockClient->shouldReceive('verifySubmission')
            ->andReturnUsing(function ($data) use (&$capturedData, $mockResult) {
                $capturedData = $data;
                return $mockResult;
            });

        $guard = new MosparoGuard(new FormStub());
        $guard->perform();

        // Verify sensitive data is removed
        $this->assertArrayNotHasKey('password', $capturedData);
        $this->assertArrayNotHasKey('password_confirm', $capturedData);
        $this->assertArrayNotHasKey('csrf_token', $capturedData);
        $this->assertArrayNotHasKey('_mosparo_submitToken', $capturedData);
        $this->assertArrayNotHasKey('_mosparo_validationToken', $capturedData);

        // Verify regular data is preserved
        $this->assertEquals('John Doe', $capturedData['name']);
        $this->assertEquals('john@example.com', $capturedData['email']);
        $this->assertEquals(
            'This is a test message with special chars: äöüß €100',
            $capturedData['message']
        );
    }

    /**
     * @test
     */
    public function testGuardExtendsBaseGuard(): void
    {
        $this->mockKirbyApp([], []);
        $guard = new MosparoGuard(new FormStub());

        $this->assertInstanceOf(\Uniform\Guards\Guard::class, $guard);
    }
}
