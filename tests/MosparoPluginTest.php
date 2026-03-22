<?php

/**
 * MosparoPlugin Test
 *
 * Basic test to verify PSR-4 autoloading and PHPUnit integration.
 * This is an infrastructure test - full Guard tests come in Phase 2.
 *
 * @package Uniform\Mosparo\Tests
 */

namespace Uniform\Mosparo\Tests;

use PHPUnit\Framework\TestCase;
use Uniform\Mosparo\MosparoPlugin;

/**
 * Tests for the MosparoPlugin class
 */
class MosparoPluginTest extends TestCase
{
    /**
     * Test that MosparoPlugin can be instantiated
     *
     * This verifies:
     * - PSR-4 autoloading is correctly configured
     * - The class exists in the expected namespace
     * - The constructor works without errors
     *
     * @return void
     */
    public function testPluginCanBeInstantiated(): void
    {
        $plugin = new MosparoPlugin();

        $this->assertInstanceOf(MosparoPlugin::class, $plugin);
    }

    /**
     * Test that plugin name is correct
     *
     * @return void
     */
    public function testPluginNameIsMosparo(): void
    {
        $plugin = new MosparoPlugin();

        $this->assertEquals('Mosparo', $plugin->getName());
    }

    /**
     * Test that plugin version is set
     *
     * @return void
     */
    public function testPluginVersionIsSet(): void
    {
        $plugin = new MosparoPlugin();

        $this->assertEquals('1.0.0', $plugin->getVersion());
    }
}
