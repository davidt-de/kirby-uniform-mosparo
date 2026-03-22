<?php

/**
 * WidgetRendererTest - Tests for WidgetRenderer
 *
 * Tests widget rendering, script loading, and data attribute handling.
 *
 * @package Uniform\Mosparo\Tests\Widget
 * @author Patrick Davidt <hallo@davidt.de>
 * @license MIT
 */

declare(strict_types=1);

namespace Uniform\Mosparo\Tests\Widget;

use Kirby\Cms\App;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Uniform\Mosparo\Config\Config;
use Uniform\Mosparo\Widget\WidgetRenderer;

/**
 * @covers \Uniform\Mosparo\Widget\WidgetRenderer
 */
class WidgetRendererTest extends MockeryTestCase
{
    /**
     * Clean up Mockery after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Create a mock Kirby App with Mosparo configuration
     *
     * @param array<string, mixed> $options Configuration options
     * @return App
     */
    private function createMockApp(array $options = []): App
    {
        $defaultOptions = [
            'getkirby-uniform.mosparo.host' => 'https://mosparo.example.com',
            'getkirby-uniform.mosparo.uuid' => 'test-uuid-12345',
            'getkirby-uniform.mosparo.publicKey' => 'test-public-key-abc',
            'getkirby-uniform.mosparo.privateKey' => 'test-private-key-xyz',
            'getkirby-uniform.mosparo.cssUrl' => null,
            'getkirby-uniform.mosparo.ignoredFields' => null,
            'getkirby-uniform.mosparo.debug' => false,
        ];

        $config = array_merge($defaultOptions, $options);

        // Mock the App using Mockery alias
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);
        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) use ($config) {
                return $config[$key] ?? null;
            });

        return $mockApp;
    }

    /**
     * Test that render returns a widget container div with mosparo-box class.
     */
    public function testRenderReturnsWidgetContainer(): void
    {
        $this->createMockApp();

        $html = WidgetRenderer::render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('class="mosparo-box"', $html);
        $this->assertStringContainsString('id="mosparo-box"', $html);
        $this->assertStringContainsString('</div>', $html);
    }

    /**
     * Test that render includes the data-mosparo-uuid attribute.
     */
    public function testRenderIncludesUuidAttribute(): void
    {
        $this->createMockApp([
            'getkirby-uniform.mosparo.uuid' => 'my-test-uuid-123',
        ]);

        $html = WidgetRenderer::render();

        $this->assertStringContainsString('data-mosparo-uuid="my-test-uuid-123"', $html);
    }

    /**
     * Test that render includes the data-mosparo-public-key attribute.
     */
    public function testRenderIncludesPublicKeyAttribute(): void
    {
        $this->createMockApp([
            'getkirby-uniform.mosparo.publicKey' => 'my-public-key-abc',
        ]);

        $html = WidgetRenderer::render();

        $this->assertStringContainsString('data-mosparo-public-key="my-public-key-abc"', $html);
    }

    /**
     * Test that render returns HTML comment when Mosparo is not configured.
     */
    public function testRenderReturnsCommentWhenNotConfigured(): void
    {
        // Mock with all null values
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);
        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) {
                return null;
            });

        $html = WidgetRenderer::render();

        $this->assertStringContainsString('<!-- Mosparo: Not configured -->', $html);
        $this->assertStringNotContainsString('<div', $html);
    }

    /**
     * Test that renderScript returns a script tag with correct src.
     */
    public function testRenderScriptReturnsScriptTag(): void
    {
        $this->createMockApp([
            'getkirby-uniform.mosparo.host' => 'https://mosparo.test.com',
        ]);

        $html = WidgetRenderer::renderScript();

        $this->assertStringContainsString('<script', $html);
        $this->assertStringContainsString('src="https://mosparo.test.com/build/mosparo-frontend.js"', $html);
        $this->assertStringContainsString('</script>', $html);
    }

    /**
     * Test that renderScript includes async and defer attributes when enabled.
     */
    public function testRenderScriptIncludesAsyncDefer(): void
    {
        $this->createMockApp();

        // With async and defer enabled (default)
        $html = WidgetRenderer::renderScript(['async' => true, 'defer' => true]);
        $this->assertStringContainsString(' async', $html);
        $this->assertStringContainsString(' defer', $html);

        // With async and defer disabled
        $html = WidgetRenderer::renderScript(['async' => false, 'defer' => false]);
        $this->assertStringNotContainsString(' async', $html);
        $this->assertStringNotContainsString(' defer', $html);
    }

    /**
     * Test that render includes custom data attributes.
     */
    public function testRenderWithCustomDataAttributes(): void
    {
        $this->createMockApp();

        $html = WidgetRenderer::render([
            'data' => [
                'theme' => 'dark',
                'size' => 'large',
            ],
        ]);

        $this->assertStringContainsString('data-mosparo-theme="dark"', $html);
        $this->assertStringContainsString('data-mosparo-size="large"', $html);
    }

    /**
     * Test that HTML output is properly escaped.
     */
    public function testRenderEscapesHtml(): void
    {
        $this->createMockApp([
            'getkirby-uniform.mosparo.uuid' => 'test<script>alert(1)</script>',
            'getkirby-uniform.mosparo.publicKey' => 'test"onclick="evil()',
        ]);

        $html = WidgetRenderer::render();

        // XSS attempts should be escaped
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onclick="evil()"', $html);

        // Should contain escaped versions
        $this->assertStringContainsString('data-mosparo-uuid="test&lt;script&gt;alert(1)&lt;/script&gt;"', $html);
        $this->assertStringContainsString('data-mosparo-public-key="test&quot;onclick=&quot;evil()"', $html);
    }

    /**
     * Test that renderScript returns comment when not configured.
     */
    public function testRenderScriptReturnsCommentWhenNotConfigured(): void
    {
        // Mock with all null values
        $mockApp = Mockery::mock('alias:' . App::class);
        $mockApp->shouldReceive('instance')->andReturn($mockApp);
        $mockApp->shouldReceive('option')
            ->andReturnUsing(function ($key) {
                return null;
            });

        $html = WidgetRenderer::renderScript();

        $this->assertStringContainsString('<!-- Mosparo: Not configured -->', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    /**
     * Test that custom ID and class are rendered correctly.
     */
    public function testRenderWithCustomIdAndClass(): void
    {
        $this->createMockApp();

        $html = WidgetRenderer::render([
            'id' => 'my-custom-id',
            'class' => 'my-custom-class another-class',
        ]);

        $this->assertStringContainsString('id="my-custom-id"', $html);
        $this->assertStringContainsString('class="my-custom-class another-class"', $html);
    }

    /**
     * Test that CSS URL is included when configured.
     */
    public function testRenderIncludesCssUrlWhenConfigured(): void
    {
        $this->createMockApp([
            'getkirby-uniform.mosparo.cssUrl' => 'https://example.com/custom-mosparo.css',
        ]);

        $html = WidgetRenderer::render();

        $this->assertStringContainsString('data-mosparo-css-url="https://example.com/custom-mosparo.css"', $html);
    }
}
