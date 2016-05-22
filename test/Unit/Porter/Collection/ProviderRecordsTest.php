<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\ProviderData;

final class ProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = new ProviderRecords(new \EmptyIterator, $data = \Mockery::mock(ProviderData::class));

        self::assertSame($data, $records->getProviderData());
    }
}
