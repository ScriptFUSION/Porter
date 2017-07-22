<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Cache\Cache;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSIONTest\FixtureFactory;

final class SuperConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImportConnector
     */
    private $superConnector;

    /**
     * @var Connector|MockInterface
     */
    private $connector;

    protected function setUp()
    {
        $this->superConnector = new ImportConnector(
            $this->connector =
                \Mockery::spy(Connector::class, Cache::class)
                    ->shouldReceive('fetch')
                    ->andReturn('foo')
                    ->getMock(),
            FixtureFactory::buildConnectionContext(CacheAdvice::MUST_CACHE())
        );
    }
}
