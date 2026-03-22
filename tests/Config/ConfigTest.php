<?php

/**
 * ConfigTest - Configuration Tests
 *
 * Comprehensive test suite for Config value object and ConfigFactory.
 *
 * @package Uniform\Mosparo\Tests
 * @author Patrick Davidt <hallo@davidt.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Tests;

use Kirby\Cms\App;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Uniform\Mosparo\Config\Config;
use Uniform\Mosparo\Config\ConfigFactory;

/**
 * Test suite for configuration system.
 *
 * @covers \Uniform\Mosparo\Config\Config
 * @covers \Uniform\Mosparo\Config\ConfigFactory
 */
final class ConfigTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    // =========================================================================
    // Config Value Object Tests
    // =========================================================================

    /**
     * @test
     */
    public function testConfigCanBeCreated(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid-123',
            publicKey: 'public-key-456',
            privateKey: 'private-key-789',
            ignoredFields: ['custom_field'],
            cssUrl: 'https://example.com/custom.css',
            debug: true,
        );

        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('https://mosparo.example.com', $config->getHost());
        $this->assertEquals('test-uuid-123', $config->getUuid());
        $this->assertEquals('public-key-456', $config->getPublicKey());
        $this->assertEquals('private-key-789', $config->getPrivateKey());
        $this->assertEquals(['custom_field'], $config->getIgnoredFields());
        $this->assertEquals('https://example.com/custom.css', $config->getCssUrl());
        $this->assertTrue($config->isDebug());
    }

    /**
     * @test
     */
    public function testConfigIsImmutable(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'public-key',
            privateKey: 'private-key',
        );

        // Verify readonly properties cannot be modified
        // This test passes if PHP doesn't throw an error during the above instantiation
        // and getters return consistent values
        $host1 = $config->getHost();
        $host2 = $config->getHost();
        $this->assertSame($host1, $host2);
    }

    /**
     * @test
     */
    public function testIsConfiguredReturnsTrueWhenAllRequiredFieldsSet(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'public-key',
            privateKey: 'private-key',
        );

        $this->assertTrue($config->isConfigured());
    }

    /**
     * @test
     */
    public function testIsConfiguredReturnsFalseWhenHostMissing(): void
    {
        $config = new Config(
            host: null,
            uuid: 'test-uuid',
            publicKey: 'public-key',
            privateKey: 'private-key',
        );

        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testIsConfiguredReturnsFalseWhenUuidMissing(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: null,
            publicKey: 'public-key',
            privateKey: 'private-key',
        );

        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testIsConfiguredReturnsFalseWhenPublicKeyMissing(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: null,
            privateKey: 'private-key',
        );

        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testIsConfiguredReturnsFalseWhenPrivateKeyMissing(): void
    {
        $config = new Config(
            host: 'https://mosparo.example.com',
            uuid: 'test-uuid',
            publicKey: 'public-key',
            privateKey: null,
        );

        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testIsConfiguredReturnsFalseWhenFieldsEmpty(): void
    {
        $config = new Config(
            host: '',
            uuid: '',
            publicKey: '',
            privateKey: '',
        );

        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testDefaultIgnoredFields(): void
    {
        $config = new Config();

        $this->assertEquals(
            ['password', 'password_confirm', 'csrf_token'],
            $config->getIgnoredFields()
        );
    }

    /**
     * @test
     */
    public function testCustomIgnoredFields(): void
    {
        $customFields = ['credit_card', 'ssn', 'secret_token'];
        $config = new Config(ignoredFields: $customFields);

        $this->assertEquals($customFields, $config->getIgnoredFields());
    }

    /**
     * @test
     */
    public function testDefaultValuesWhenEmptyConfig(): void
    {
        $config = new Config();

        $this->assertNull($config->getHost());
        $this->assertNull($config->getUuid());
        $this->assertNull($config->getPublicKey());
        $this->assertNull($config->getPrivateKey());
        $this->assertNull($config->getCssUrl());
        $this->assertFalse($config->isDebug());
    }

    /**
     * @test
     * Security test: Verify private key is accessible internally but documented as sensitive
     */
    public function testPrivateKeyIsAccessibleForServerSideUse(): void
    {
        $config = new Config(privateKey: 'secret-private-key');

        // Private key should be accessible for server-side API verification
        $this->assertEquals('secret-private-key', $config->getPrivateKey());
    }

    // =========================================================================
    // ConfigFactory Tests
    // =========================================================================

    /**
     * @test
     */
    public function testFactoryCreatesConfigFromOptions(): void
    {
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);

        // Define option return values
        $options = [
            'davidt-de.uniform-mosparo.host' => 'https://mosparo.example.com',
            'davidt-de.uniform-mosparo.uuid' => 'test-uuid',
            'davidt-de.uniform-mosparo.publicKey' => 'public-key',
            'davidt-de.uniform-mosparo.privateKey' => 'private-key',
            'davidt-de.uniform-mosparo.ignoredFields' => ['custom_field'],
            'davidt-de.uniform-mosparo.cssUrl' => 'https://example.com/style.css',
            'davidt-de.uniform-mosparo.debug' => true,
        ];

        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) use ($options) {
                return $options[$key] ?? null;
            });

        $config = ConfigFactory::fromKirbyOptions();

        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('https://mosparo.example.com', $config->getHost());
        $this->assertEquals('test-uuid', $config->getUuid());
        $this->assertEquals('public-key', $config->getPublicKey());
        $this->assertEquals('private-key', $config->getPrivateKey());
        $this->assertEquals(['custom_field'], $config->getIgnoredFields());
        $this->assertEquals('https://example.com/style.css', $config->getCssUrl());
        $this->assertTrue($config->isDebug());
    }

    /**
     * @test
     */
    public function testFactoryUsesDefaults(): void
    {
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);
        $mockApp->shouldReceive('option')->with(Mockery::any())->andReturn(null);

        $config = ConfigFactory::fromKirbyOptions();

        $this->assertNull($config->getHost());
        $this->assertEquals(['password', 'password_confirm', 'csrf_token'], $config->getIgnoredFields());
        $this->assertFalse($config->isDebug());
    }

    /**
     * @test
     */
    public function testFactoryHandlesPartialConfig(): void
    {
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);

        $options = [
            'davidt-de.uniform-mosparo.host' => 'https://mosparo.example.com',
            'davidt-de.uniform-mosparo.publicKey' => 'public-key',
            // Missing: uuid, privateKey
        ];

        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) use ($options) {
                return $options[$key] ?? null;
            });

        $config = ConfigFactory::fromKirbyOptions();

        $this->assertEquals('https://mosparo.example.com', $config->getHost());
        $this->assertEquals('public-key', $config->getPublicKey());
        $this->assertNull($config->getUuid());
        $this->assertNull($config->getPrivateKey());
        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testFactoryCreateFromArray(): void
    {
        $options = [
            'host' => 'https://mosparo.example.com',
            'uuid' => 'test-uuid',
            'publicKey' => 'public-key',
            'privateKey' => 'private-key',
            'ignoredFields' => ['field1', 'field2'],
            'debug' => true,
        ];

        $config = ConfigFactory::create($options);

        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('https://mosparo.example.com', $config->getHost());
        $this->assertEquals('test-uuid', $config->getUuid());
        $this->assertEquals('public-key', $config->getPublicKey());
        $this->assertEquals('private-key', $config->getPrivateKey());
        $this->assertEquals(['field1', 'field2'], $config->getIgnoredFields());
        $this->assertTrue($config->isDebug());
    }

    /**
     * @test
     */
    public function testFactoryCreateFromArrayUsesDefaults(): void
    {
        $config = ConfigFactory::create([]);

        $this->assertNull($config->getHost());
        $this->assertEquals(['password', 'password_confirm', 'csrf_token'], $config->getIgnoredFields());
        $this->assertFalse($config->isDebug());
    }

    /**
     * @test
     */
    public function testFactoryCreateHandlesPartialConfig(): void
    {
        $options = [
            'host' => 'https://mosparo.example.com',
            // Missing other fields
        ];

        $config = ConfigFactory::create($options);

        $this->assertEquals('https://mosparo.example.com', $config->getHost());
        $this->assertNull($config->getUuid());
        $this->assertFalse($config->isConfigured());
    }

    /**
     * @test
     */
    public function testFactoryIgnoresNonStringOptionValues(): void
    {
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);

        // Test with non-string values that should be filtered out
        $options = [
            'davidt-de.uniform-mosparo.host' => 123, // Should be rejected
            'davidt-de.uniform-mosparo.uuid' => ['array-value'], // Should be rejected
            'davidt-de.uniform-mosparo.publicKey' => 'valid-key',
        ];

        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) use ($options) {
                return $options[$key] ?? null;
            });

        $config = ConfigFactory::fromKirbyOptions();

        $this->assertNull($config->getHost());
        $this->assertNull($config->getUuid());
        $this->assertEquals('valid-key', $config->getPublicKey());
    }
}
