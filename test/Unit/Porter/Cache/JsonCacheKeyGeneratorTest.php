<?php
namespace ScriptFUSIONTest\Unit\Porter\Cache;

use ScriptFUSION\Porter\Cache\JsonCacheKeyGenerator;
use ScriptFUSIONTest\Stubs\TestOptions;

final class JsonCacheKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateCacheKey()
    {
        $options = new TestOptions;
        $options->setFoo('(baz@quz\quux/quuz)');

        self::assertSame(
            '["bar",."foo".".baz.quz..quux.quuz.".]',
            (new JsonCacheKeyGenerator)->generateCacheKey('bar', $options->copy())
        );
    }
}
