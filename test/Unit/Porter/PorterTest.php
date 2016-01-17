<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\Provider;

final class PorterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndGetProvider()
    {
        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('getName')->andReturn('foo');

        $porter = new Porter;
        $porter->addProvider($provider);

        $this->assertSame($provider, $porter->getProvider('foo'));
    }
}
