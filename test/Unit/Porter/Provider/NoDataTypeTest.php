<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\NoDataType;

final class NoDataTypeTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        self::assertFalse((new NoDataType)->fetch(\Mockery::mock(Connector::class))->valid());
    }
}
