<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider\Resource;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Provider\Resource\NullResource;
use ScriptFUSIONTest\FixtureFactory;

final class NullResourceTest extends \PHPUnit_Framework_TestCase
{
    public function test(): void
    {
        self::assertFalse(
            (new NullResource)->fetch(
                new ImportConnector(\Mockery::mock(Connector::class), FixtureFactory::buildConnectionContext())
            )->valid()
        );
    }
}
