<?php
namespace ScriptFUSIONTest\Unit\Porter\Connector;

use ScriptFUSION\Porter\Connector\NullConnector;

final class NullConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->assertNull((new NullConnector)->fetch('foo'));
    }
}
