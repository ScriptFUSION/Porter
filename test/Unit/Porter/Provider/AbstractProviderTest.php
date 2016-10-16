<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Provider\AbstractProvider;
use ScriptFUSION\Porter\Provider\ForeignResourceException;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSIONTest\MockFactory;

final class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var AbstractProvider
     */
    private $provider;

    /**
     * @var MockInterface|Connector
     */
    private $connector;

    protected function setUp()
    {
        $this->createProviderMock();
    }

    private function createProviderMock($connector = null)
    {
        $this->provider = \Mockery::mock(
            AbstractProvider::class,
            [$this->connector = $connector ?: \Mockery::mock(Connector::class)]
        )->makePartial();
    }

    private function setupCachingConnector()
    {
        $this->createProviderMock($connector = \Mockery::mock(CachingConnector::class));

        return $connector;
    }

    public function testFetchWithoutOptions()
    {
        self::assertSame(
            'foo',
            $this->provider->fetch(
                MockFactory::mockResource($this->provider)
                    ->shouldReceive('fetch')
                    ->with($this->connector, null)
                    ->andReturn('foo')
                    ->getMock()
            )
        );
    }

    /**
     * Tests that a clone of the provider's options are passed to ProviderResource::fetch().
     */
    public function testFetchWithOptions()
    {
        $this->setOptions($options = \Mockery::mock(EncapsulatedOptions::class));

        $this->provider->fetch(
            MockFactory::mockResource($this->provider)
                ->shouldReceive('fetch')
                ->with($this->connector, \Mockery::on(
                    function (EncapsulatedOptions $argument) use ($options) {
                        self::assertNotSame($options, $argument);

                        return get_class($options) === get_class($argument);
                    }
                ))
                ->getMock()
        );
    }

    public function testFetchForeignProvider()
    {
        $this->setExpectedException(ForeignResourceException::class);

        $this->provider->fetch(
            \Mockery::mock(ProviderResource::class)
                ->shouldReceive('getProviderClassName')
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
        $this->setExpectedException(CacheUnavailableException::class);

        $this->provider->enableCache();
    }

    public function testEnableCacheSucceeds()
    {
        $this->setupCachingConnector()->shouldReceive('enableCache');

        $this->provider->enableCache();
    }

    public function testDisableCacheFails()
    {
        $this->setExpectedException(CacheUnavailableException::class);

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

    public function testOptions()
    {
        $this->setOptions($options = \Mockery::mock(EncapsulatedOptions::class));

        self::assertSame($options, $this->getOptions());
    }

    private function getOptions()
    {
        return call_user_func(
            \Closure::bind(
                function () {
                    return $this->getOptions();
                },
                $this->provider
            )
        );
    }

    private function setOptions(EncapsulatedOptions $options)
    {
        call_user_func(
            \Closure::bind(
                function () use ($options) {
                    $this->setOptions($options);
                },
                $this->provider
            )
        );
    }
}
