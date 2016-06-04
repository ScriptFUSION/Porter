<?php
namespace ScriptFUSIONTest\Integration\Specification;

use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;

/**
 * @see StaticDataImportSpecification
 */
final class StaticDataImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = (new Porter)->import(new StaticDataImportSpecification(new \ArrayIterator(['foo'])));

        self::assertSame('foo', $records->current());
    }
}
