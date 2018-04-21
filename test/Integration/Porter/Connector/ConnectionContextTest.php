<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter\Connector;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\ConnectionContext;

/**
 * @see ConnectionContext
 */
final class ConnectionContextTest extends TestCase
{
    /**
     * Tests that the cache option passed to the constructor matches the cache option accessor.
     */
    public function testMustCache(): void
    {
        self::assertTrue((new ConnectionContext(true))->mustCache());
        self::assertFalse((new ConnectionContext(false))->mustCache());
    }
}
