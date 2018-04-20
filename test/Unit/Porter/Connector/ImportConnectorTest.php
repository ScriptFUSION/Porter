<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Porter\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ConnectorWrapper;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSIONTest\FixtureFactory;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Tests that when fetching, the specified context and source are passed verbatim to the underlying connector.
     */
    public function testCallGraph(): void
    {
        $connector = new ImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->with(
                    $context = FixtureFactory::buildConnectionContext(),
                    $source = 'bar'
                )->once()
                ->andReturn($output = 'foo')
                ->getMock(),
            $context
        );

        self::assertSame($output, $connector->fetch($source));
    }

    /**
     * Tests that when context specifies no cache required and a normal connector is used, fetch succeeds.
     */
    public function testFetchCacheDisabled(): void
    {
        $connector = new ImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->andReturn($output = 'foo')
                ->getMock(),
            FixtureFactory::buildConnectionContext()
        );

        self::assertSame($output, $connector->fetch('bar'));
    }

    /**
     * Tests that when context specifies cache required and a caching connector is used, fetch succeeds.
     */
    public function testFetchCacheEnabled(): void
    {
        $connector = new ImportConnector(
            \Mockery::mock(CachingConnector::class, [\Mockery::mock(Connector::class)])
                ->shouldReceive('fetch')
                ->andReturn($output = 'foo')
                ->getMock(),
            FixtureFactory::buildConnectionContext(true)
        );

        self::assertSame($output, $connector->fetch('bar'));
    }

    /**
     * Tests that when context specifies cache required but a non-caching connector is used, an exception is thrown.
     */
    public function testFetchCacheEnabledButNotAvailable(): void
    {
        $this->expectException(CacheUnavailableException::class);

        new ImportConnector(
            \Mockery::mock(Connector::class),
            FixtureFactory::buildConnectionContext(true)
        );
    }

    /**
     * Tests that getting the wrapped connector returns a clone of the original connector passed to the constructor.
     */
    public function testGetWrappedConnector(): void
    {
        $connector = new ImportConnector(
            $wrappedConnector = \Mockery::mock(Connector::class),
            FixtureFactory::buildConnectionContext()
        );

        self::assertNotSame($wrappedConnector, $connector->getWrappedConnector());
        self::assertSame(\get_class($wrappedConnector), \get_class($connector->getWrappedConnector()));
    }

    /**
     * Tests that setting the provider exception handler twice produces an exception the second time.
     */
    public function testSetExceptionHandlerTwice(): void
    {
        $connector = new ImportConnector(
            $wrappedConnector = \Mockery::mock(Connector::class),
            FixtureFactory::buildConnectionContext()
        );

        $connector->setExceptionHandler($handler = \Mockery::mock(FetchExceptionHandler::class));

        $this->expectException(\LogicException::class);
        $connector->setExceptionHandler($handler);
    }

    /**
     * Tests that finding the base connector returns the connector at the bottom of a ConnectorWrapper stack.
     */
    public function testFindBaseConnector(): void
    {
        $connector = new ImportConnector(
            \Mockery::mock(Connector::class, ConnectorWrapper::class)
                ->shouldReceive('getWrappedConnector')
                    ->andReturn($baseConnector = \Mockery::mock(Connector::class))
                ->getMock(),
            FixtureFactory::buildConnectionContext()
        );

        self::assertSame($baseConnector, $connector->findBaseConnector());
    }
}
