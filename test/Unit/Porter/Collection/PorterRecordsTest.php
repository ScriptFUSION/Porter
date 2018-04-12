<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class PorterRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test(): void
    {
        $records = new PorterRecords(
            \Mockery::mock(RecordCollection::class),
            $specification = \Mockery::mock(ImportSpecification::class)
        );

        self::assertSame($specification, $records->getSpecification());
    }
}
