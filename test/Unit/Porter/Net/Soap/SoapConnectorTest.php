<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Soap;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Net\Soap\SoapConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

final class SoapConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvalidOptionsType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        (new SoapConnector('foo'))->fetch('foo', \Mockery::mock(EncapsulatedOptions::class));
    }
}
