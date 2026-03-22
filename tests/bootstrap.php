<?php

/**
 * Test Bootstrap
 *
 * Loads the Composer autoloader and sets up the test environment.
 *
 * @package Uniform\Mosparo\Tests
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set test constants
define('TESTS_DIR', __DIR__);
define('SRC_DIR', dirname(__DIR__) . '/src');

// Configure error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Note: Mockery configuration will be added when needed
// Mockery::globalConfiguration()->allowMockingNonExistentMethods(false);
