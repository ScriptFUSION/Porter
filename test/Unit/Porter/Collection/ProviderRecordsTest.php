<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Porter\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

final class ProviderRecordsTest extends TestCase
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
