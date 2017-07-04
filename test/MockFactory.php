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
     * @param \Iterator $return
     *
     * @return ProviderResource|MockInterface
     */
    public static function mockResource(Provider $provider, \Iterator $return = null)
    {
        $resource = \Mockery::mock(ProviderResource::class)
            ->shouldReceive('getProviderClassName')
                ->andReturn(get_class($provider))
                ->byDefault()
            ->shouldReceive('fetch')
                ->andReturnUsing(function () {
                    yield 'foo';
                })
                ->byDefault()
            ->getMock();

        if ($return !== null) {
            $resource->shouldReceive('fetch')->andReturn($return);
        }

        return $resource;
    }
}
