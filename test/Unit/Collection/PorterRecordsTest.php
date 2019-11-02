<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Specification\ImportSpecification;

/**
 * @see PorterRecords
 */
final class PorterRecordsTest extends TestCase
{
    /**
     * Tests that the specification passed at construction time is the same as that retrieved from the accessor method.
     */
    public function testGetSpecification(): void
    {
        $records = new PorterRecords(
            \Mockery::mock(RecordCollection::class),
            $specification = \Mockery::mock(ImportSpecification::class)
        );

        self::assertSame($specification, $records->getSpecification());
    }
}
