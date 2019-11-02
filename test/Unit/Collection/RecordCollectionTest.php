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

    public function testFindParent(): void
    {
        /**
         * @var RecordCollection $collection1
         * @var RecordCollection $collection2
         * @var RecordCollection $collection3
         */
        $collection3 = \Mockery::mock(
            RecordCollection::class,
            [
                $it = \Mockery::mock(\Iterator::class),
                $collection2 = \Mockery::mock(
                    RecordCollection::class,
                    [
                        $it,
                        $collection1 = \Mockery::mock(RecordCollection::class)->makePartial(),
                    ]
                )->makePartial(),
            ]
        )->makePartial();

        self::assertSame($collection1, $collection1->findFirstCollection());
        self::assertSame($collection1, $collection2->findFirstCollection());
        self::assertSame($collection1, $collection3->findFirstCollection());
    }

    /**
     * Tests that when a RecordCollection yields a non-array datum, an exception is thrown.
     */
    public function testNonArrayYield(): void
    {
        /** @var RecordCollection $collection */
        $collection = \Mockery::mock(
            RecordCollection::class,
            [new \ArrayIterator(['foo'])]
        )->makePartial();

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('must be of the type array');
        $collection->current();
    }
}