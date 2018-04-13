<?php
namespace ScriptFUSIONTest\Unit\Porter\Cache;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Cache\JsonCacheKeyGenerator;
use ScriptFUSIONTest\Stubs\TestOptions;

final class JsonCacheKeyGeneratorTest extends TestCase
{
    public function testGenerateCacheKey(): void
    {
        $options = new TestOptions;
        $options->setFoo('(baz@quz\quux/quuz)');

        self::assertSame(
            '["bar",."foo".".baz.quz..quux.quuz.".]',
            (new JsonCacheKeyGenerator)->generateCacheKey('bar', $options->copy())
        );
    }
}
