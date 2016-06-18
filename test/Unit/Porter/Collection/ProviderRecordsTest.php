<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\ProviderDataFetcher;

final class ProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = new ProviderRecords(
            \Mockery::mock(\Iterator::class),
            $dataFetcher = \Mockery::mock(ProviderDataFetcher::class)
        );

        self::assertSame($dataFetcher, $records->getDataFetcher());
    }
}
