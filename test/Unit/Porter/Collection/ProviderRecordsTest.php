<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\ProviderDataType;

final class ProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = new ProviderRecords(new \EmptyIterator, $providerDataType = \Mockery::mock(ProviderDataType::class));

        self::assertSame($providerDataType, $records->getProviderData());
    }
}
