<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Caching;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Cache\MemoryCache;

final class CachingConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    public function test()
    {
        $memoryCache = new MemoryCache();

        $connector = new CachingTestConnector();
        $connector->setCache($memoryCache);
        $source = '@foo@';

        $structure = [$source, []];
        self::assertSame('bar', $connector->fetch($source, null));

        $hash = str_replace(str_split('{}()/\@:'), '', json_encode($structure));
        $hashTest = '["foo",[]]';
        self::assertSame('bar', $memoryCache->getItem($hash)->get());

        self::assertSame($hash, $hashTest);
        self::assertSame($hashTest, $memoryCache->getItem($hash)->getKey());
    }
}
