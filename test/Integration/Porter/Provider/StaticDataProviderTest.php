<?php
namespace ScriptFUSIONTest\Integration\Porter\Provider;

use ScriptFUSION\Porter\ImportSpecification;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\StaticData;
use ScriptFUSION\Porter\Provider\StaticDataProvider;

final class StaticDataProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $porter = (new Porter)->addProvider(new StaticDataProvider);
        $records = $porter->import(new ImportSpecification(new StaticData(new \ArrayIterator(['foo']))));

        $this->assertSame('foo', $records->current());
    }
}
