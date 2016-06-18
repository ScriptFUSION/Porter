<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\NullFetcher;

final class NullFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        self::assertFalse((new NullFetcher)->fetch(\Mockery::mock(Connector::class))->valid());
    }
}
