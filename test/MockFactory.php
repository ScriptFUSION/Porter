<?php
namespace ScriptFUSIONTest;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\Resource;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    public static function mockImportSpecification(Resource $resource = null)
    {
        return \Mockery::mock(ImportSpecification::class, [$resource ?: \Mockery::mock(Resource::class)]);
    }

    /**
     * @param Provider $provider
     *
     * @return MockInterface|Resource
     */
    public static function mockResource(Provider $provider)
    {
        return \Mockery::spy(Resource::class)
            ->shouldReceive('getProviderClassName')
            ->andReturn(get_class($provider))
            ->getMock();
    }
}
