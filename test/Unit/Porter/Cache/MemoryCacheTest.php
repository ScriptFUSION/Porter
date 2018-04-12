<?php
namespace ScriptFUSIONTest\Unit\Porter\Cache;

use ScriptFUSION\Porter\Cache\CacheItem;
use ScriptFUSION\Porter\Cache\InvalidArgumentException;
use ScriptFUSION\Porter\Cache\MemoryCache;

final class MemoryCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var MemoryCache */
    private $cache;

    private $items;

    protected function setUp()
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
        $this->cache->clear();

        self::assertEmpty($this->cache->getArrayCopy());
    }

    public function testDeleteItem(): void
    {
        $this->cache->deleteItem('foo');

        self::assertFalse($this->cache->hasItem('foo'));
    }

    public function testDeleteItems(): void
    {
        $this->cache->deleteItems(['foo']);

        self::assertEmpty($this->cache->getArrayCopy());
    }

    public function testDeleteInvalidItem(): void
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $this->cache->deleteItems(['foo', 'bar']);
    }

    public function testSave(): void
    {
        $this->cache->save($this->cache->getItem('bar')->set('baz'));

        self::assertSame('baz', $this->cache->getItem('bar')->get());
    }

    public function testSaveDeferred(): void
    {
        $this->cache->saveDeferred($this->cache->getItem('bar')->set('baz'));

        self::assertSame('baz', $this->cache->getItem('bar')->get());
    }

    public function testCommit(): void
    {
        self::assertTrue($this->cache->commit());
    }
}
