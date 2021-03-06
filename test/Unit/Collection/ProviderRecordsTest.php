<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

/**
 * @see ProviderRecords
 */
final class ProviderRecordsTest extends TestCase
{
    /**
     * Tests that the resource passed at construction time is the same as that retrieved from the accessor method.
     */
    public function testGetResource(): void
    {
        $records = new ProviderRecords(
            \Mockery::mock(\Iterator::class),
            $resource = \Mockery::mock(ProviderResource::class)
        );

        self::assertSame($resource, $records->getResource());
    }
}
