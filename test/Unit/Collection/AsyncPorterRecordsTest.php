<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\AsyncPorterRecords;
use ScriptFUSION\Porter\Collection\AsyncRecordCollection;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;

/**
 * @see AsyncPorterRecords
 */
final class AsyncPorterRecordsTest extends TestCase
{
    /**
     * Tests that the specification passed at construction time is the same as that retrieved from the accessor method.
     */
    public function testGetSpecification(): void
    {
        $records = new AsyncPorterRecords(
            \Mockery::spy(AsyncRecordCollection::class),
            $specification = \Mockery::mock(AsyncImportSpecification::class)
        );

        self::assertSame($specification, $records->getSpecification());
    }
}
