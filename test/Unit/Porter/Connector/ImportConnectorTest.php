<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSIONTest\FixtureFactory;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that when fetching, the specified context and source are passed verbatim to the underlying connector.
     */
    public function testCallGraph()
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
    public function testFetchCacheDisabled()
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
    public function testFetchCacheEnabled()
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
    public function testFetchCacheEnabledButNotAvailable()
    {
        $this->setExpectedException(CacheUnavailableException::class);

        new ImportConnector(
            \Mockery::mock(Connector::class),
            FixtureFactory::buildConnectionContext(true)
        );
    }

    /**
     * Tests that getting the wrapped connector returns a clone of the original connector passed to the constructor.
     */
    public function testGetWrappedConnector()
    {
        $connector = new ImportConnector(
            $wrappedConnector = \Mockery::mock(Connector::class),
            FixtureFactory::buildConnectionContext()
        );

        self::assertNotSame($wrappedConnector, $connector->getWrappedConnector());
        self::assertSame(get_class($wrappedConnector), get_class($connector->getWrappedConnector()));
    }

    /**
     * Tests that setting the provider exception handler twice produces an exception the second time.
     */
    public function testSetExceptionHandlerTwice()
    {
        $connector = new ImportConnector(
            $wrappedConnector = \Mockery::mock(Connector::class),
            FixtureFactory::buildConnectionContext()
        );

        $connector->setExceptionHandler($handler = \Mockery::mock(FetchExceptionHandler::class));

        $this->setExpectedException(\LogicException::class);
        $connector->setExceptionHandler($handler);
    }
}
