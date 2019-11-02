<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Connector\Recoverable;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;

/**
 * @see StatelessRecoverableExceptionHandler
 */
final class StatelessRecoverableExceptionHandlerTest extends TestCase
{
    /**
     * Tests that the initialize() method does not throw any exception. This test exists solely for code coverage.
     *
     * @doesNotPerformAssertions
     */
    public function testInitialize(): void
    {
        (new StatelessRecoverableExceptionHandler(static function (): void {
            // Intentionally empty.
        }))->initialize();

        // PHPUnit asserts no exception is thrown.
    }
}
