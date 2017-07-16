<?php
namespace ScriptFUSIONTest\Integration\Porter\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheKeyGenerator;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Cache\JsonCacheKeyGenerator;
use ScriptFUSION\Porter\Cache\MemoryCache;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\Stubs\TestOptions;

final class CachingConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var CachingConnector|MockInterface $connector
     */
    private $connector;

    /**
     * @var ConnectionContext
     */
    private $context;

    /**
     * @var TestOptions
     */
    private $options;

    protected function setUp()
    {
        $this->connector = \Mockery::mock(CachingConnector::class, [])->makePartial()
            ->shouldReceive('fetchFreshData')
            ->andReturn('foo', 'bar')
            ->getMock();

        $this->context = FixtureFactory::buildConnectionContext(CacheAdvice::SHOULD_CACHE());

        $this->options = new TestOptions;
    }

    public function testCacheEnabled()
    {
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz', $this->options));
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz', $this->options));
    }

    public function testCacheDisabled()
    {
        $context = FixtureFactory::buildConnectionContext(CacheAdvice::SHOULD_NOT_CACHE());

        self::assertSame('foo', $this->connector->fetch($context, 'baz', $this->options));
        self::assertSame('bar', $this->connector->fetch($context, 'baz', $this->options));
    }

    public function testCacheAvailable()
    {
        self::assertTrue($this->connector->isCacheAvailable());
    }

    public function testGetSetCache()
    {
        self::assertInstanceOf(CacheItemPoolInterface::class, $this->connector->getCache());
        self::assertNotSame($cache = new MemoryCache, $this->connector->getCache());

        $this->connector->setCache($cache);
        self::assertSame($cache, $this->connector->getCache());
    }

    public function testGetSetCacheKeyGenerator()
    {
        self::assertInstanceOf(CacheKeyGenerator::class, $this->connector->getCacheKeyGenerator());
        self::assertNotSame($cacheKeyGenerator = new JsonCacheKeyGenerator, $this->connector->getCacheKeyGenerator());

        $this->connector->setCacheKeyGenerator($cacheKeyGenerator);
        self::assertSame($cacheKeyGenerator, $this->connector->getCacheKeyGenerator());
    }

    public function testCacheBypassedForDifferentOptions()
    {
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz', $this->options));

        $this->options->setFoo('bar');
        self::assertSame('bar', $this->connector->fetch($this->context, 'baz', $this->options));
    }

    public function testCacheUsedForDifferentOptionsInstance()
    {
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz', $this->options));
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz', clone $this->options));
    }

    /**
     * Tests that when the cache key generator returns the same hash the same data is fetched, and when it does not,
     * fresh data is fetched.
     */
    public function testCacheKeyGenerator()
    {
        $this->connector->setCacheKeyGenerator(
            \Mockery::mock(CacheKeyGenerator::class)
                ->shouldReceive('generateCacheKey')
                ->with($source = 'baz', $this->options->copy())
                ->andReturn('qux', 'qux', 'quux')
                ->getMock()
        );

        self::assertSame('foo', $this->connector->fetch($this->context, $source, $this->options));
        self::assertSame('foo', $this->connector->fetch($this->context, $source, $this->options));
        self::assertSame('bar', $this->connector->fetch($this->context, $source, $this->options));
    }

    public function testFetchThrowsInvalidCacheKeyExceptionOnNonStringCacheKey()
    {
        $this->connector->setCacheKeyGenerator(
            \Mockery::mock(CacheKeyGenerator::class)
                ->shouldReceive('generateCacheKey')
                ->andReturn(1)
                ->getMock()
        );

        $this->setExpectedException(InvalidCacheKeyException::class, 'Cache key must be a string.');
        $this->connector->fetch($this->context, 'baz', $this->options);
    }

    public function testFetchThrowsInvalidCacheKeyExceptionOnNonPSR6CompliantCacheKey()
    {
        $this->connector->setCacheKeyGenerator(
            \Mockery::mock(CacheKeyGenerator::class)
                ->shouldReceive('generateCacheKey')
                ->andReturn(CachingConnector::RESERVED_CHARACTERS)
                ->getMock()
        );

        $this->setExpectedException(InvalidCacheKeyException::class, 'contains one or more reserved characters');
        $this->connector->fetch($this->context, 'baz', $this->options);
    }

    public function testNullAndEmptyOptionsAreEquivalent()
    {
        /** @var EncapsulatedOptions $options */
        $options = \Mockery::mock(EncapsulatedOptions::class)->shouldReceive('copy')->andReturn([])->getMock();

        self::assertEmpty($options->copy());
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz', $options));
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));
    }

    /**
     * Tests that the default cache key generator does not output reserved characters even when comprised of options
     * containing them.
     */
    public function testCacheKeyExcludesReservedCharacters()
    {
        $reservedCharacters = CachingConnector::RESERVED_CHARACTERS;

        $this->connector->setCache($cache = \Mockery::spy(CacheItemPoolInterface::class));

        $cache->shouldReceive('hasItem')
            ->andReturnUsing(
                function ($key) use ($reservedCharacters) {
                    foreach (str_split($reservedCharacters) as $reservedCharacter) {
                        self::assertNotContains($reservedCharacter, $key);
                    }
                }
            )->once()
            ->shouldReceive('getItem')->andReturnSelf()
            ->shouldReceive('set')->andReturn(\Mockery::mock(CacheItemInterface::class));

        $this->connector->fetch($this->context, $reservedCharacters, (new TestOptions)->setFoo($reservedCharacters));
    }
}
