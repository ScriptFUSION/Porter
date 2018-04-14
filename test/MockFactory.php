<?php
namespace ScriptFUSIONTest;

use Amp\Delayed;
use Amp\Producer;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Connector\AsyncConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Provider\AsyncProvider;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    /**
     * @return Provider|AsyncProvider|MockInterface
     */
    public static function mockProvider()
    {
        return \Mockery::namedMock(uniqid(Provider::class, false), Provider::class, AsyncProvider::class)
            ->shouldReceive('getConnector')
                ->andReturn(
                    \Mockery::mock(Connector::class)
                        ->shouldReceive('fetch')
                        ->andReturn('foo')
                        ->getMock()
                        ->byDefault()
                )
                ->byDefault()
            ->shouldReceive('getAsyncConnector')
                ->andReturn(
                    \Mockery::mock(AsyncConnector::class)
                        ->shouldReceive('fetchAsync')
                        ->andReturn(new Delayed(0, 'foo'))
                        ->getMock()
                )
            ->getMock()
        ;
    }

    /**
     * @return ProviderResource|AsyncResource|MockInterface
     */
    public static function mockResource(Provider $provider, \Iterator $return = null)
    {
        $resource = \Mockery::mock(ProviderResource::class, AsyncResource::class)
            ->shouldReceive('getProviderClassName')
                ->andReturn(\get_class($provider))
            ->shouldReceive('fetch')
                ->andReturnUsing(static function (ImportConnector $connector) {
                    return new \ArrayIterator([[$connector->fetch('foo')]]);
                })
                ->byDefault()
            ->shouldReceive('fetchAsync')
                ->andReturnUsing(static function (ImportConnector $connector) {
                    return new Producer(static function (\Closure $emit) use ($connector) {
                        $emit([yield $connector->fetchAsync('foo')]);
                    });
                })

            ->getMock()
        ;

        if ($return !== null) {
            $resource->shouldReceive('fetch')->andReturn($return);
        }

        return $resource;
    }
}
