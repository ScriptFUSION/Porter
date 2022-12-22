<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Cache;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Cache\CacheItem;
use ScriptFUSION\Porter\Cache\InvalidArgumentException;
use ScriptFUSION\Porter\Cache\MemoryCache;

/**
 * @see MemoryCache
 */
final class MemoryCacheTest extends TestCase
{
    private MemoryCache $cache;

    private array $items;

    protected function setUp(): void
    {
        $this->cache = new MemoryCache($this->items = ['foo' => 'bar']);
    }

    public function testGetItem(): void
    {
        $item = $this->cache->getItem('foo');

        self::assertTrue($item->isHit());
        self::assertSame('bar', $item->get());

        self::assertFalse($this->cache->getItem('baz')->isHit());
    }

    public function testGetItems(): void
    {
        self::assertEmpty(iterator_to_array($this->cache->getItems()));

        /** @var CacheItem $item */
        $item = $this->cache->getItems(['foo'])->current();
        self::assertTrue($item->isHit());
        self::assertSame('bar', $item->get());

        $item = $this->cache->getItems(['baz'])->current();
        self::assertFalse($item->isHit());
    }

    public function testHasItem(): void
    {
        self::assertTrue($this->cache->hasItem('foo'));
        self::assertFalse($this->cache->hasItem('bar'));
    }

    public function testClear(): void
    {
        self::assertTrue($this->cache->clear());

        self::assertEmpty($this->cache->getArrayCopy());
    }

    public function testDeleteItem(): void
    {
        self::assertTrue($this->cache->deleteItem('foo'));

        self::assertFalse($this->cache->hasItem('foo'));
    }

    public function testDeleteItems(): void
    {
        self::assertTrue($this->cache->deleteItems(['foo']));

        self::assertEmpty($this->cache->getArrayCopy());
    }

    public function testDeleteInvalidItem(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->deleteItems(['foo', 'bar']);
    }

    public function testSave(): void
    {
        self::assertTrue($this->cache->save($this->cache->getItem('bar')->set('baz')));

        self::assertSame('baz', $this->cache->getItem('bar')->get());
    }

    public function testSaveDeferred(): void
    {
        self::assertTrue($this->cache->saveDeferred($this->cache->getItem('bar')->set('baz')));

        self::assertSame('baz', $this->cache->getItem('bar')->get());
    }

    public function testCommit(): void
    {
        self::assertTrue($this->cache->commit());
    }
}
