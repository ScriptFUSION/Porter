<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSIONTest\FixtureFactory;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that when fetching, the specified context, source and options are passed verbatim to the underlying
     * connector.
     */
    public function testCallGraph()
    {
        $connector = new ImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->with(
                    $context = FixtureFactory::buildConnectionContext(),
                    $source = 'bar',
                    $options = \Mockery::mock(EncapsulatedOptions::class)
                )->once()
                ->andReturn($output = 'foo')
                ->getMock(),
            $context
        );

        self::assertSame($output, $connector->fetch($source, $options));
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
            \Mockery::mock(CachingConnector::class)
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
}
