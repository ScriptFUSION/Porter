<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\AsyncFilteredRecords;
use ScriptFUSION\Porter\Collection\AsyncRecordCollection;

/**
 * @see AsyncFilteredRecords
 */
final class AsyncFilteredRecordsTest extends TestCase
{
    /**
     * Tests that the filter passed at construction time is the same as that retrieved from the accessor method.
     */
    public function testGetResource(): void
    {
        $records = new AsyncFilteredRecords(
            \Mockery::mock(\Iterator::class),
            \Mockery::mock(AsyncRecordCollection::class),
            $callable = [$this, __FUNCTION__]
        );

        self::assertSame($callable, $records->getFilter());
    }
}
