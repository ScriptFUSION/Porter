<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider\Resource;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\Resource\NullResource;

final class NullResourceTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        self::assertFalse((new NullResource)->fetch(\Mockery::mock(Connector::class))->valid());
    }
}
