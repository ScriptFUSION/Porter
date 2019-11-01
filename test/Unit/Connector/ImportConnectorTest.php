<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ConnectorWrapper;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSIONTest\FixtureFactory;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Tests that when fetching, the specified source is passed verbatim to the underlying connector.
     */
    public function testCallGraph(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->with($source = 'bar')->once()
                ->andReturn($output = 'foo')
                ->getMock()
        );

        self::assertSame($output, $connector->fetch($source));
    }

    /**
     * Tests that when no cache required and a normal connector is used, fetch succeeds.
     */
    public function testFetchCacheDisabled(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->andReturn($output = 'foo')
                ->getMock()
        );

        self::assertSame($output, $connector->fetch('bar'));
    }

    /**
     * Tests that when cache required and a caching connector is used, fetch succeeds.
     */
    public function testFetchCacheEnabled(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(CachingConnector::class, [\Mockery::mock(Connector::class)])
                ->shouldReceive('fetch')
                ->andReturn($output = 'foo')
                ->getMock(),
            null,
            1,
            true
        );

        self::assertSame($output, $connector->fetch('bar'));
    }

    /**
     * Tests that when cache is disabled and a caching connector is used, fetch succeeds from the wrapped connector.
     */
    public function testFetchCacheDisabledWithCachingConnector(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(CachingConnector::class, [\Mockery::mock(Connector::class)])
                ->shouldReceive('getWrappedConnector')
                ->andReturn(
                    \Mockery::mock(Connector::class)
                        ->shouldReceive('fetch')
                        ->andReturn($output = 'foo')
                        ->getMock()
                )
                ->getMock()
        );

        self::assertSame($output, $connector->fetch('bar'));
    }

    /**
     * Tests that when cache required but a non-caching connector is used, an exception is thrown.
     */
    public function testFetchCacheEnabledButNotAvailable(): void
    {
        $this->expectException(CacheUnavailableException::class);

        FixtureFactory::buildImportConnector(\Mockery::mock(Connector::class), null, 1, true);
    }

    /**
     * Tests that getting the wrapped connector returns a clone of the original connector passed to the constructor.
     */
    public function testGetWrappedConnector(): void
    {
        $connector = FixtureFactory::buildImportConnector($wrappedConnector = \Mockery::mock(Connector::class));

        self::assertNotSame($wrappedConnector, $connector->getWrappedConnector());
        self::assertSame(\get_class($wrappedConnector), \get_class($connector->getWrappedConnector()));
    }

    /**
     * Tests that setting the provider exception handler twice produces an exception the second time.
     */
    public function testSetExceptionHandlerTwice(): void
    {
        $connector = FixtureFactory::buildImportConnector($wrappedConnector = \Mockery::mock(Connector::class));

        $connector->setRecoverableExceptionHandler(
            $handler = \Mockery::mock(RecoverableExceptionHandler::class)
        );

        $this->expectException(\LogicException::class);
        $connector->setRecoverableExceptionHandler($handler);
    }

    /**
     * Tests that finding the base connector returns the connector at the bottom of a ConnectorWrapper stack.
     */
    public function testFindBaseConnector(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class, ConnectorWrapper::class)
                ->shouldReceive('getWrappedConnector')
                ->andReturn($baseConnector = \Mockery::mock(Connector::class))
                ->getMock()
        );

        self::assertSame($baseConnector, $connector->findBaseConnector());
    }
}
