<?php
declare(strict_types=1);

namespace ScriptFUSIONTest;

use Amp\Delayed;
use Amp\Iterator;
use Amp\Producer;
use Amp\Promise;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Connector\AsyncConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Provider\AsyncProvider;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    /**
     * @return Provider|AsyncProvider|MockInterface
     */
    public static function mockProvider()
    {
        return \Mockery::namedMock(uniqid(Provider::class), Provider::class, AsyncProvider::class)
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
                ->byDefault()
            ->getMock()
        ;
    }

    /**
     * @return ProviderResource|AsyncResource|MockInterface
     */
    public static function mockResource(Provider $provider, \Iterator $return = null)
    {
        $resource = \Mockery::mock(ProviderResource::class, AsyncResource::class, SingleRecordResource::class)
            ->shouldReceive('getProviderClassName')
                ->andReturn(\get_class($provider))
            ->shouldReceive('fetch')
                ->andReturnUsing(static function (ImportConnector $connector): \Iterator {
                    return new \ArrayIterator([[$connector->fetch(\Mockery::mock(DataSource::class))]]);
                })
                ->byDefault()
            ->shouldReceive('fetchAsync')
                ->andReturnUsing(static function (ImportConnector $connector): Iterator {
                    return new Producer(static function (\Closure $emit) use ($connector): \Generator {
                        yield $emit([yield $connector->fetchAsync(\Mockery::mock(DataSource::class))]);
                    });
                })
                ->byDefault()
            ->getMock()
        ;

        if ($return !== null) {
            $resource->shouldReceive('fetch')->andReturn($return);
        }

        return $resource;
    }

    /**
     * @return Promise|MockInterface
     */
    public static function mockPromise()
    {
        return \Mockery::mock(Promise::class)
            ->expects('onResolve')
                ->andReturnUsing(static function (\Closure $onResolve): void {
                    $onResolve(null, null);
                })
            ->getMock()
        ;
    }
}
