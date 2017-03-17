<?php
namespace ScriptFUSIONTest;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    /**
     * @param Provider $provider
     *
     * @return MockInterface|ProviderResource
     */
    public static function mockResource(Provider $provider)
    {
        return \Mockery::mock(ProviderResource::class)
            ->shouldReceive('getProviderClassName')
            ->andReturn(get_class($provider))
            ->byDefault()
            ->getMock();
    }
}
