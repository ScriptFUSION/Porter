<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Cache\InvalidCacheKeyException;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;

/**
 * @see CachingConnector
 */
final class CachingConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var CachingConnector $connector */
    private $connector;

    /** @var Connector|MockInterface */
    private $wrappedConnector;

    /** @var DataSource|MockInterface */
    private $source;

    protected function setUp(): void
    {
        $this->connector = new CachingConnector(
            $this->wrappedConnector = \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                    ->andReturn('foo', 'bar')
                ->getMock()
        );

        $this->source = \Mockery::mock(DataSource::class)
            ->shouldReceive('computeHash')
                ->andReturn('1')
                ->byDefault()
            ->getMock()
        ;
    }

    /**
     * Tests that the same result is returned because the wrapped connector is bypassed.
     */
    public function testCacheEnabled(): void
    {
        self::assertSame('foo', $this->connector->fetch($this->source));
        self::assertSame('foo', $this->connector->fetch($this->source));
    }

    /**
     * Tests that when sources are the same but options are different, the cache is not reused.
     */
    public function testCacheBypassedForDifferentOptions(): void
    {
        self::assertSame('foo', $this->connector->fetch($this->source));

        $this->source->shouldReceive('computeHash')->andReturn('2');
        self::assertSame('bar', $this->connector->fetch($this->source));
    }

    /**
     * That that when the generated cache key contains non-compliant PSR-6 characters,
     * InvalidCacheKeyException is thrown.
     */
    public function testValidateCacheKey(): void
    {
        $this->source->shouldReceive('computeHash')->andReturn(CachingConnector::RESERVED_CHARACTERS);

        $this->expectException(InvalidCacheKeyException::class);
        $this->expectExceptionMessage('contains one or more reserved characters');
        $this->connector->fetch($this->source);
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
}
