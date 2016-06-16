<?php
namespace ScriptFUSIONTest\Unit\Porter\Cache;

use ScriptFUSION\Porter\Cache\CacheItem;
use ScriptFUSION\Porter\Cache\NotImplementedException;

final class CacheItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var CacheItem */
    private $item;

    protected function setUp()
    {
        $this->item = $this->createCacheItem();
    }

    public function testGetKey()
    {
        self::assertSame('foo', $this->item->getKey());
    }

    public function testGet()
    {
        self::assertSame('bar', $this->item->get());
    }

    public function testIsHit()
    {
        self::assertTrue($this->item->isHit());
    }

    public function testSet()
    {
        self::assertSame('baz', $this->item->set('baz')->get());
    }

    public function testExpiresAt()
    {
        $this->setExpectedException(NotImplementedException::class);

        $this->item->expiresAt(null);
    }

    public function testExpiresAfter()
    {
        $this->setExpectedException(NotImplementedException::class);

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
