<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider\DataSource;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\DataSource\NullDataSource;

final class NullDataSourceTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        self::assertFalse((new NullDataSource)->fetch(\Mockery::mock(Connector::class))->valid());
    }
}
