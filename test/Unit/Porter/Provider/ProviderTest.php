<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Cache\CacheOperationProhibitedException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\ForeignDataFetcherException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderDataFetcher;

final class ProviderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var Provider */
    private $provider;

    /** @var MockInterface */
    private $connector;

    protected function setUp()
    {
        $this->createProviderMock();
    }

    private function createProviderMock($connector = null)
    {
        $this->provider = \Mockery::mock(
            Provider::class,
            [$this->connector = $connector ?: \Mockery::mock(Connector::class)]
        )->makePartial();
    }

    private function setupCachingConnector()
    {
        $this->createProviderMock($connector = \Mockery::mock(CachingConnector::class));

        return $connector;
    }

    public function testFetch()
    {
        self::assertSame(
            'foo',
            $this->provider->fetch(
                \Mockery::mock(ProviderDataFetcher::class)
                    ->shouldReceive('fetch')
                    ->with($this->connector)
                    ->andReturn('foo')
                    ->getMock()
                    ->shouldReceive('getProviderName')
                    ->andReturn(get_class($this->provider))
                    ->getMock()
            )
        );
    }

    public function testFetchForeignProvider()
    {
        $this->setExpectedException(ForeignDataFetcherException::class);

        $this->provider->fetch(
            \Mockery::mock(ProviderDataFetcher::class)
                ->shouldReceive('getProviderName')
                ->andReturn('foo')
                ->getMock()
        );
    }

    public function testGetConnector()
    {
        self::assertSame($this->connector, $this->provider->getConnector());
    }

    public function testEnableCacheFails()
    {
        $this->setExpectedException(CacheOperationProhibitedException::class);

        $this->provider->enableCache();
    }

    public function testEnableCacheSucceeds()
    {
        $this->setupCachingConnector()->shouldReceive('enableCache');

        $this->provider->enableCache();
    }

    public function testDisableCacheFails()
    {
        $this->setExpectedException(CacheOperationProhibitedException::class);

        $this->provider->disableCache();
    }

    public function testDisableCacheSucceeds()
    {
        $this->setupCachingConnector()->shouldReceive('disableCache');

        $this->provider->disableCache();
    }

    public function testCacheDisabledWhenConnectorDoesNotSupportCaching()
    {
        self::assertFalse($this->provider->isCacheEnabled());
    }

    public function testCacheEnabledMirrorsCachingConnector()
    {
        $this->setupCachingConnector()->shouldReceive('isCacheEnabled')->andReturn(true, false);

        self::assertTrue($this->provider->isCacheEnabled());
        self::assertFalse($this->provider->isCacheEnabled());
    }
}
