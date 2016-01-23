<?php
namespace ScriptFUSIONTest\Functional;

use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;

final class StaticDataTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = (new Porter)->import(new StaticDataImportSpecification(new \ArrayIterator(['foo'])));

        $this->assertSame('foo', $records->current());
    }
}
