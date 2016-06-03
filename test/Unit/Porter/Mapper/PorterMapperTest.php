<?php
namespace ScriptFUSIONTest\Unit\Porter\Mapper;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Mapper\AnonymousMapping;
use ScriptFUSION\Mapper\Strategy\Strategy;
use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapper\PorterMapper;
use ScriptFUSION\Porter\Porter;

final class PorterMapperTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    public function testMap()
    {
        $mapper = new PorterMapper(\Mockery::mock(Porter::class));

        /** @var RecordCollection $records */
        $records = \Mockery::mock(
            RecordCollection::class,
            [new \ArrayIterator([[1], [2], [3]])]
        )->makePartial();

        $mappedRecords = $mapper->mapRecords(
            $records,
            new AnonymousMapping([$strategy = \Mockery::mock(Strategy::class)])
        );

        $strategy->shouldReceive('__invoke')->andReturnUsing(function ($data) {
            return $data[0] * $data[0];
        });

        self::assertInstanceOf(MappedRecords::class, $mappedRecords);
        self::assertSame([[1], [4], [9]], iterator_to_array($mappedRecords));
    }
}
