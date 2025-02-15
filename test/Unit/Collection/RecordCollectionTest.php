<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\RecordCollection;

/**
 * @see RecordCollection
 */
final class RecordCollectionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Tests that for each member in a stack of RecordCollections, the first collection always points to the
     * innermost collection.
     */
    public function testFindFirstCollection(): void
    {
        $collection3 = \Mockery::mock(
            RecordCollection::class,
            [
                $it = \Mockery::mock(\Iterator::class),
                $collection2 = \Mockery::mock(
                    RecordCollection::class,
                    [
                        $it,
                        $collection1 = \Mockery::mock(RecordCollection::class, [$it])->makePartial(),
                    ]
                )->makePartial(),
            ]
        )->makePartial();

        self::assertSame($collection1, $collection1->findFirstCollection());
        self::assertSame($collection1, $collection2->findFirstCollection());
        self::assertSame($collection1, $collection3->findFirstCollection());
    }

    /**
     * Tests that when a RecordCollection yields a non-array datum, the datum is returned as-is.
     */
    public function testNonArrayYield(): void
    {
        /** @var RecordCollection $collection */
        $collection = \Mockery::mock(
            RecordCollection::class,
            [new \ArrayIterator([$datum = 'foo'])]
        )->makePartial();

        self::assertSame($datum, $collection->current());
    }
}
