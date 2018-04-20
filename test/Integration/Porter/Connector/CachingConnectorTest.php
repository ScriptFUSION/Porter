<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ScriptFUSION\Porter\Cache\CacheKeyGenerator;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ConnectorOptions;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\Stubs\TestOptions;

/**
 * @see CachingConnector
 */
final class CachingConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var CachingConnector $connector
     */
    private $connector;

    /**
     * @var Connector|ConnectorOptions|MockInterface
     */
    private $wrappedConnector;

    /**
     * @var ConnectionContext
     */
    private $context;

    /**
     * @var TestOptions
     */
    private $options;

    protected function setUp(): void
    {
        $this->options = new TestOptions;

        $this->connector = new CachingConnector(
            $this->wrappedConnector = \Mockery::mock(Connector::class, ConnectorOptions::class)
                ->shouldReceive('fetch')
                    ->andReturn('foo', 'bar')
                ->shouldReceive('getOptions')
                    ->andReturn($this->options)
                    ->byDefault()
                ->shouldReceive('clone')
                    ->andReturn(null)
                ->getMock()
        );

        $this->context = FixtureFactory::buildConnectionContext(true);
    }

    /**
     * Tests that when cache is enabled, the same result is returned because the wrapped connector is bypassed.
     */
    public function testCacheEnabled(): void
    {
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));
    }

    /**
     * Tests that when cache is disabled, different results are returned from the wrapped connector.
     */
    public function testCacheDisabled(): void
    {
        // The default connection context has caching disabled.
        $context = FixtureFactory::buildConnectionContext();

        self::assertSame('foo', $this->connector->fetch($context, 'baz'));
        self::assertSame('bar', $this->connector->fetch($context, 'baz'));
    }

    /**
     * Tests that when sources are the same but options are different, the cache is not reused.
     */
    public function testCacheBypassedForDifferentOptions(): void
    {
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));

        $this->options->setFoo('bar');
        self::assertSame('bar', $this->connector->fetch($this->context, 'baz'));
    }

    /**
     * Tests that when the same options are specified by two different object instances, the cache is reused.
     */
    public function testCacheUsedForDifferentOptionsInstance(): void
    {
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));

        $this->wrappedConnector->shouldReceive('getOptions')->andReturn($options = clone $this->options);
        self::assertNotSame($this->options, $options);
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));

        // Ensure new options have really taken effect by changing option. Cache should no longer be used.
        $options->setFoo('bar');
        self::assertSame('bar', $this->connector->fetch($this->context, 'baz'));
    }

    public function testNullAndEmptyOptionsAreEquivalent(): void
    {
        /** @var EncapsulatedOptions $options */
        $options = \Mockery::mock(EncapsulatedOptions::class)->shouldReceive('copy')->andReturn([])->getMock();
        $this->wrappedConnector->shouldReceive('getOptions')->andReturn($options);
        self::assertEmpty($this->wrappedConnector->getOptions()->copy());

        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));
        self::assertSame('foo', $this->connector->fetch($this->context, 'baz'));
    }

    /**
     * Tests that the default cache key generator does not output reserved characters even when comprised of options
     * containing them.
     */
    public function testCacheKeyExcludesReservedCharacters(): void
    {
        $reservedCharacters = CacheKeyGenerator::RESERVED_CHARACTERS;

        $connector = $this->createConnector($cache = \Mockery::spy(CacheItemPoolInterface::class));

        $cache->shouldReceive('hasItem')
            ->andReturnUsing(
                function ($key) use ($reservedCharacters): void {
                    foreach (str_split($reservedCharacters) as $reservedCharacter) {
                        self::assertNotContains($reservedCharacter, $key);
                    }
                }
            )->once()
            ->shouldReceive('getItem')->andReturnSelf()
            ->shouldReceive('set')->andReturn(\Mockery::mock(CacheItemInterface::class))
        ;

        $connector->fetch($this->context, $reservedCharacters);
    }

    /**
     * Tests that when the cache key generator returns the same key the same data is fetched, and when it does not,
     * fresh data is fetched.
     */
    public function testCacheKeyGenerator(): void
    {
        $connector = $this->createConnector(
            null,
            \Mockery::mock(CacheKeyGenerator::class)
                ->shouldReceive('generateCacheKey')
                ->with($source = 'baz', $this->options->copy())
                ->andReturn('qux', 'qux', 'quux')
                ->getMock()
        );

        self::assertSame('foo', $connector->fetch($this->context, $source));
        self::assertSame('foo', $connector->fetch($this->context, $source));
        self::assertSame('bar', $connector->fetch($this->context, $source));
    }

    public function testFetchThrowsInvalidCacheKeyExceptionOnNonPSR6CompliantCacheKey(): void
    {
        $connector = $this->createConnector(
            null,
            \Mockery::mock(CacheKeyGenerator::class)
                ->shouldReceive('generateCacheKey')
                ->andReturn(CacheKeyGenerator::RESERVED_CHARACTERS)
                ->getMock()
        );

        $this->expectException(InvalidCacheKeyException::class);
        $this->expectExceptionMessage('contains one or more reserved characters');
        $connector->fetch($this->context, 'baz');
    }

    /**
     * Tests that getting the wrapped connector returns exactly the same connector as constructed with.
     */
    public function testGetWrappedConnector(): void
    {
        self::assertSame($this->wrappedConnector, $this->connector->getWrappedConnector());
    }

    /**
     * Tests that cloning the caching connector also clones the wrapped connector.
     */
    public function testClone(): void
    {
        $clone = clone $this->connector;

        self::assertNotSame($this->wrappedConnector, $clone->getWrappedConnector());
    }

    private function createConnector(
        MockInterface $cache = null,
        MockInterface $cacheKeyGenerator = null
    ): CachingConnector {
        return new CachingConnector($this->wrappedConnector, $cache, $cacheKeyGenerator);
    }
}
