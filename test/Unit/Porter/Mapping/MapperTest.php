<?php
namespace ScriptFUSIONTest\Unit\Porter\Mapping;

use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapping\Mapper;
use ScriptFUSION\Porter\Mapping\Mapping;
use ScriptFUSION\Porter\Mapping\Resolver;

final class MapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $mapper = new Mapper($resolver = \Mockery::mock(Resolver::class));

        $resolver->shouldReceive('resolveMapping')->andReturnUsing(function ($_, $record) {
            return [$record[0] * $record[0]];
        });

        $records = \Mockery::mock(
            RecordCollection::class,
            [new \ArrayIterator([[1], [2], [3]])]
        )->makePartial();

        $mappedRecords = $mapper->map($records, \Mockery::mock(Mapping::class));

        $this->assertInstanceOf(MappedRecords::class, $mappedRecords);
        $this->assertSame([[1], [4], [9]], iterator_to_array($mappedRecords));
    }
}
