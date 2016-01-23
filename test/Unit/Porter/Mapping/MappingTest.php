<?php
namespace ScriptFUSIONTest\Unit\Porter\Mapping;

use ScriptFUSION\Porter\Mapping\Mapping;

final class MappingTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->assertSame(['foo' => 'bar'], (new TestMapping)->getArrayCopy());
    }
}

final class TestMapping extends Mapping
{
    public function createMap()
    {
        return ['foo' => 'bar'];
    }
}
