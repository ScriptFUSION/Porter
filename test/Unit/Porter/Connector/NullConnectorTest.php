<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\NullConnector;
use ScriptFUSIONTest\FixtureFactory;

final class NullConnectorTest extends TestCase
{
    public function test(): void
    {
        self::assertNull((new NullConnector)->fetch(FixtureFactory::buildConnectionContext(), 'foo'));
    }
}
