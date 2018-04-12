<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use ScriptFUSION\Porter\Connector\NullConnector;
use ScriptFUSIONTest\FixtureFactory;

final class NullConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function test(): void
    {
        self::assertNull((new NullConnector)->fetch(FixtureFactory::buildConnectionContext(), 'foo'));
    }
}
