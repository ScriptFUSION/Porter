<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Provider\ProviderData;

final class CountableProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $data = range(1, 10);

        $records = new CountableProviderRecords(
            new \ArrayIterator($data),
            count($data),
            \Mockery::mock(ProviderData::class)
        );

        self::assertCount(count($data), $records);
        self::assertSame($data, iterator_to_array($records));
    }
}
