<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector\FetchExceptionHandler;

use ScriptFUSION\Porter\Connector\FetchExceptionHandler\StatelessFetchExceptionHandler;

/**
 * @see StatelessFetchExceptionHandler
 */
final class StatelessFetchExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that the initialize() method does not throw any exception. This test exists solely for code coverage.
     */
    public function testInitialize()
    {
        (new StatelessFetchExceptionHandler(static function () {
            // Intentionally empty.
        }))->initialize();

        // PHPUnit asserts no exception is thrown.
    }
}
