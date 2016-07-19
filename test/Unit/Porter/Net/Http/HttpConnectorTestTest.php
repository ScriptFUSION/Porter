<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Http;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Net\Http\HttpConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

final class HttpConnectorTestTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvalidOptionsType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        (new HttpConnector)->fetch('foo', \Mockery::mock(EncapsulatedOptions::class));
    }
}
