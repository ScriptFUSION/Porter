<?php
namespace ScriptFUSIONTest\Unit\Porter\Collection;

use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

final class ProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    public function test(): void
    {
        $records = new ProviderRecords(
            \Mockery::mock(\Iterator::class),
            $resource = \Mockery::mock(ProviderResource::class)
        );

        self::assertSame($resource, $records->getResource());
    }
}
