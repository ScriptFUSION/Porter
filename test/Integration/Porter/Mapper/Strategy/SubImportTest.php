<?php
namespace ScriptFUSIONTest\Integration\Porter\Mapper\Strategy;

use ScriptFUSION\Porter\Mapper\Strategy\SubImport;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;

/**
 * @see SubImport
 * @group Mapper
 */
final class SubImportTest extends \PHPUnit_Framework_TestCase
{
    public function testSpecificationCallbackCanCreateSpecification()
    {
        $record = 'foo';

        $import = new SubImport(function () use ($record) {
            return new StaticDataImportSpecification(new \ArrayIterator([$record]));
        });
        $import->setPorter(new Porter);

        $records = $import(null);

        self::assertSame($record, $records[0]);
    }
}
