<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;

final class ProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = new ProviderRecords(
            \Mockery::mock(\Iterator::class),
            $dataSource = \Mockery::mock(ProviderDataSource::class)
        );

        self::assertSame($dataSource, $records->getDataSource());
    }
}
