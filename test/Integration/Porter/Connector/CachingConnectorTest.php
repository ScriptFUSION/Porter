<?php
namespace ScriptFUSIONTest\Integration\Porter\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSIONTest\Stubs\TestOptions;

final class CachingConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var CachingConnector|MockInterface $connector */
    private $connector;

    /** @var TestOptions */
    private $options;

    protected function setUp()
    {
        $this->connector = \Mockery::mock(CachingConnector::class, [])->makePartial()
            ->shouldReceive('fetchFreshData')
            ->andReturn('foo', 'bar')
            ->getMock();

        $this->options = new TestOptions;
    }

    public function testCacheEnabled()
    {
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
    }

    public function testCacheDisabled()
    {
        $this->connector->disableCache();

        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
        self::assertSame('bar', $this->connector->fetch('baz', $this->options));
    }

    public function testCache()
    {
        /** @var CacheItemPoolInterface $cache */
        $cache = \Mockery::mock(CacheItemPoolInterface::class);
        $this->connector->setCache($cache);

        self::assertSame($cache, $this->connector->getCache());
    }

    public function testCacheBypassedForDifferentOptions()
    {
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));

        $this->options->setFoo('bar');

        self::assertSame('bar', $this->connector->fetch('baz', $this->options));
    }

    public function testCacheUsedForDifferentOptionsInstance()
    {
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
        self::assertSame('foo', $this->connector->fetch('baz', clone $this->options));
    }

    public function testNullAndEmptyAreEquivalent()
    {
        /** @var EncapsulatedOptions $options */
        $options = \Mockery::mock(EncapsulatedOptions::class)->shouldReceive('copy')->andReturn([])->getMock();

        self::assertEmpty($options->copy());
        self::assertSame('foo', $this->connector->fetch('baz', $options));

        self::assertSame('foo', $this->connector->fetch('baz'));
    }

    public function testEnableCache()
    {
        self::assertTrue($this->connector->isCacheEnabled());

        $this->connector->disableCache();
        self::assertFalse($this->connector->isCacheEnabled());

        $this->connector->enableCache();
        self::assertTrue($this->connector->isCacheEnabled());
    }
}
