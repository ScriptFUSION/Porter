<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Collection;

use Amp\Iterator;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\AsyncProviderRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;

/**
 * @see ProviderRecords
 */
final class AsyncProviderRecordsTest extends TestCase
{
    /**
     * Tests that the resource passed at construction time is the same as that retrieved from the accessor method.
     */
    public function testGetResource(): void
    {
        $records = new AsyncProviderRecords(
            \Mockery::mock(Iterator::class),
            $resource = \Mockery::mock(AsyncResource::class)
        );

        self::assertSame($resource, $records->getResource());
    }
}
