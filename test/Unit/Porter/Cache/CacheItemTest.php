<?php
namespace ScriptFUSIONTest\Unit\Porter\Cache;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Cache\CacheItem;
use ScriptFUSION\Porter\Cache\NotImplementedException;

final class CacheItemTest extends TestCase
{
    /** @var CacheItem */
    private $item;

    protected function setUp()
    {
        $this->item = $this->createCacheItem();
    }

    public function testGetKey(): void
    {
        self::assertSame('foo', $this->item->getKey());
    }

    public function testGet(): void
    {
        self::assertSame('bar', $this->item->get());
    }

    public function testIsHit(): void
    {
        self::assertTrue($this->item->isHit());
    }

    public function testSet(): void
    {
        self::assertSame('baz', $this->item->set('baz')->get());
    }

    public function testExpiresAt(): void
    {
        $this->expectException(NotImplementedException::class);

        $this->item->expiresAt(null);
    }

    public function testExpiresAfter(): void
    {
        $this->expectException(NotImplementedException::class);

        $this->item->expiresAfter(null);
    }

    private function createCacheItem()
    {
        return call_user_func(
            \Closure::bind(
                function () {
                    return new self('foo', 'bar', true);
                },
                null,
                CacheItem::class
            )
        );
    }
}
