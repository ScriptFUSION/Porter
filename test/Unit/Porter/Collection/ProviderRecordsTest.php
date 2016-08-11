<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\Resource;

final class ProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $records = new ProviderRecords(
            \Mockery::mock(\Iterator::class),
            $resource = \Mockery::mock(Resource::class)
        );

        self::assertSame($resource, $records->getResource());
    }
}
