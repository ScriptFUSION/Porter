<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\AsyncFilteredRecords;
use ScriptFUSION\Porter\Collection\AsyncPorterRecords;
use ScriptFUSION\Porter\Collection\AsyncProviderRecords;
use ScriptFUSION\Porter\Collection\AsyncRecordCollection;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;

/**
 * @see AsyncRecordCollection
 */
final class AsyncRecordCollectionTest extends TestCase
{
    public function testGetPreviousCollection(): void
    {
        $records = new AsyncPorterRecords(
            $previous = \Mockery::spy(AsyncRecordCollection::class),
            \Mockery::mock(AsyncImportSpecification::class)
        );

        self::assertSame($previous, $records->getPreviousCollection());
    }

    /**
     * Tests that for each member in a stack of AsyncRecordCollections, the first collection always points to the
     * innermost collection.
     */
    public function testFindFirstCollection(): void
    {
        $collection3 = new AsyncFilteredRecords(
            $iterator = \Mockery::spy(\Iterator::class),
            $collection2 = new AsyncPorterRecords(
                $collection1 =
                    new AsyncProviderRecords($iterator, \Mockery::mock(AsyncResource::class)),
                \Mockery::mock(AsyncImportSpecification::class)
            ),
            [$this, __FUNCTION__]
        );

        self::assertSame($collection1, $collection1->findFirstCollection());
        self::assertSame($collection1, $collection2->findFirstCollection());
        self::assertSame($collection1, $collection3->findFirstCollection());
    }
}
