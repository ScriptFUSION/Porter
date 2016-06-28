<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\NullDataFetcher;

final class NullFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        self::assertFalse((new NullDataFetcher)->fetch(\Mockery::mock(Connector::class))->valid());
    }
}
