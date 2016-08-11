<?php
namespace ScriptFUSIONTest;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    public static function mockImportSpecification(ProviderDataSource $dataSource = null)
    {
        return \Mockery::mock(ImportSpecification::class, [$dataSource ?: \Mockery::mock(ProviderDataSource::class)]);
    }

    /**
     * @param Provider $provider
     *
     * @return MockInterface|ProviderDataSource
     */
    public static function mockDataSource(Provider $provider)
    {
        return \Mockery::spy(ProviderDataSource::class)
            ->shouldReceive('getProviderClassName')
            ->andReturn(get_class($provider))
            ->getMock();
    }
}
