<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\ProviderNotFoundException;

final class PorterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndGetProvider()
    {
        $porter = (new Porter)->addProvider($provider = \Mockery::mock(Provider::class));

        $this->assertSame($provider, $porter->getProvider(get_class($provider)));
    }

    public function testGetInvalidProvider()
    {
        $this->setExpectedException(ProviderNotFoundException::class);

        (new Porter)->getProvider('foo');
    }
}
