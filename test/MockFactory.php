<?php
declare(strict_types=1);

namespace ScriptFUSIONTest;

use Mockery\MockInterface;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\AsyncConnector;
use ScriptFUSION\Porter\Connector\AsyncDataSource;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Provider\AsyncProvider;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;
use ScriptFUSION\StaticClass;
use function Amp\async;
use function Amp\delay;

final class MockFactory
{
    use StaticClass;

    public static function mockProvider(): Provider|AsyncProvider|MockInterface
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
                        ->andReturnUsing(static function (): mixed {
                            delay(0);

                            return 'foo';
                        })
                        ->getMock()
                )
                ->byDefault()
            ->getMock()
        ;
    }

    public static function mockResource(Provider $provider, \Iterator $return = null, bool $single = false)
        : ProviderResource|AsyncResource|MockInterface
    {
        /** @var ProviderResource|AsyncResource|MockInterface $resource */
        $resource = \Mockery::mock(
            ...array_merge(
                [ProviderResource::class, AsyncResource::class],
                $single ? [SingleRecordResource::class] : []
            )
        )
            ->shouldReceive('getProviderClassName')
                ->andReturn(\get_class($provider))
            ->shouldReceive('fetch')
                ->andReturnUsing(static function (ImportConnector $connector): \Iterator {
                    return new \ArrayIterator([[$connector->fetch(\Mockery::mock(DataSource::class))]]);
                })
                ->byDefault()
            ->shouldReceive('fetchAsync')
                ->andReturnUsing(static function (ImportConnector $connector): \Iterator {
                    return new \ArrayIterator([[$connector->fetchAsync(\Mockery::mock(AsyncDataSource::class))]]);
                })
                ->byDefault()
            ->getMock()
        ;

        if ($return !== null) {
            $resource->shouldReceive('fetch')->andReturn($return);
        }

        return $resource;
    }

    public static function mockSingleRecordResource(Provider $provider): ProviderResource|AsyncResource|MockInterface
    {
        return self::mockResource($provider, null, true);
    }

    public static function mockThrottle(): Throttle|MockInterface
    {
        return \Mockery::mock(Throttle::class)
            ->allows('async')
                ->andReturnUsing(fn (\Closure $closure, mixed ...$args) => async($closure, ...$args))
                ->byDefault()
            ->getMock()
        ;
    }
}
