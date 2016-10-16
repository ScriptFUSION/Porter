<?php
namespace ScriptFUSIONTest;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    public static function mockImportSpecification(ProviderResource $resource = null)
    {
        return \Mockery::mock(ImportSpecification::class, [$resource ?: \Mockery::mock(ProviderResource::class)]);
    }

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
            ->shouldReceive('getProviderTag')
            ->andReturn(null)
            ->byDefault()
            ->getMock();
    }
}
