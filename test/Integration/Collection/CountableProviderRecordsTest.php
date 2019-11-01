<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Collection;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

/**
 * @see CountableProviderRecords
 */
final class CountableProviderRecordsTest extends TestCase
{
    /**
     * Tests that counting the collection matches the passed count value.
     */
    public function testCount(): void
    {
        $records = new CountableProviderRecords(
            new \EmptyIterator,
            $count = 10,
            \Mockery::mock(ProviderResource::class)
        );

        self::assertCount($count, $records);
    }
}
