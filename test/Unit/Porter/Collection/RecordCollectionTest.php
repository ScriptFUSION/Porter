<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\RecordCollection;

final class RecordCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testFindParent()
    {
        /**
         * @var RecordCollection $collection1
         * @var RecordCollection $collection2
         * @var RecordCollection $collection3
         */
        $collection3 = \Mockery::mock(
            RecordCollection::class,
            [
                $it = new \EmptyIterator,
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
}
