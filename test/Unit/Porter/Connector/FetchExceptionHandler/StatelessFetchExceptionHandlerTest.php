<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector\FetchExceptionHandler;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\StatelessFetchExceptionHandler;

/**
 * @see StatelessFetchExceptionHandler
 */
final class StatelessFetchExceptionHandlerTest extends TestCase
{
    /**
     * Tests that the initialize() method does not throw any exception. This test exists solely for code coverage.
     *
     * @doesNotPerformAssertions
     */
    public function testInitialize(): void
    {
        (new StatelessFetchExceptionHandler(static function () {
            // Intentionally empty.
        }))->initialize();

        // PHPUnit asserts no exception is thrown.
    }
}
