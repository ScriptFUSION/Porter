<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Cache\Cache;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\SuperConnector;
use ScriptFUSIONTest\FixtureFactory;

final class SuperConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SuperConnector
     */
    private $superConnector;

    /**
     * @var Connector|MockInterface
     */
    private $connector;

    protected function setUp()
    {
        $this->superConnector = new SuperConnector(
            $this->connector =
                \Mockery::spy(Connector::class, Cache::class)
                    ->shouldReceive('fetch')
                    ->andReturn('foo')
                    ->getMock(),
            FixtureFactory::buildConnectionContext(CacheAdvice::MUST_CACHE())
        );
    }

    public function testCacheUnavailable()
    {
        $this->setExpectedException(CacheUnavailableException::class, 'unavailable');

        $this->superConnector->fetch('foo');
    }

    /**
     * Tests that when cache is optional no exception is thrown when connector does not support caching.
     */
    public function testCacheOptional()
    {
        self::assertSame(
            'foo',
            (new SuperConnector(
                $this->connector,
                FixtureFactory::buildConnectionContext(CacheAdvice::SHOULD_CACHE())
            ))->fetch('bar')
        );
    }
}
