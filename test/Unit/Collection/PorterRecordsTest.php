<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Import\Import;

/**
 * @see PorterRecords
 */
final class PorterRecordsTest extends TestCase
{
    /**
     * Tests that the import passed at construction time is the same as that retrieved from the accessor method.
     */
    public function testGetImport(): void
    {
        $records = new PorterRecords(
            \Mockery::spy(RecordCollection::class),
            $import = \Mockery::mock(Import::class)
        );

        self::assertSame($import, $records->getImport());
    }
}
