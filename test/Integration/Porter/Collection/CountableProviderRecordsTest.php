<?php
namespace ScriptFUSIONTest\Integration\Porter\Collection;

use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

/**
 * @see CountableProviderRecords
 */
final class CountableProviderRecordsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that counting the collection matches the passed count value.
     */
    public function testCount()
    {
        $records = new CountableProviderRecords(
            new \EmptyIterator,
            $count = 10,
            \Mockery::mock(ProviderResource::class)
        );

        self::assertCount($count, $records);
    }
}
