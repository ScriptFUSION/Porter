<?php
declare(strict_types=1);

namespace ScriptFUSIONTest;

use Mockery\MockInterface;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;
use ScriptFUSION\StaticClass;
use function Amp\async;
use function Amp\delay;

final class MockFactory
{
    use StaticClass;

    public static function mockProvider(): Provider|MockInterface
    {
        return \Mockery::namedMock(uniqid(Provider::class), Provider::class)
            ->shouldReceive('getConnector')
                ->andReturn(
                    \Mockery::mock(Connector::class)
                        ->shouldReceive('fetch')
                        ->andReturnUsing(static function (): mixed {
                            delay(0);

                            return 'foo';
                        })
                        ->getMock()
                        ->byDefault()
                )
                ->byDefault()
            ->getMock()
        ;
    }

    public static function mockResource(Provider $provider, \Iterator $return = null, bool $single = false)
        : ProviderResource|MockInterface
    {
        /** @var ProviderResource|MockInterface $resource */
        $resource = \Mockery::mock(
            ...array_merge(
                [ProviderResource::class],
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
            ->getMock()
        ;

        if ($return !== null) {
            $resource->shouldReceive('fetch')->andReturn($return);
        }

        return $resource;
    }

    public static function mockSingleRecordResource(Provider $provider): ProviderResource|MockInterface
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
