<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Soap;

use ScriptFUSION\Porter\Net\Soap\SoapOptions;

final class SoapOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testOptionDefaults()
    {
        $options = new SoapOptions;

        self::assertSame([], $options->getParameters());
        self::assertNull($options->getVersion());
        self::assertNull($options->getCompression());
        self::assertNull($options->getKeepAlive());
    }

    public function testParameters()
    {
        $options = (new SoapOptions)->setParameters($params = ['foo' => 'bar']);

        self::assertSame($params, $options->getParameters());
    }

    public function testVersion()
    {
        $options = (new SoapOptions)->setVersion(SOAP_1_2);

        self::assertSame(SOAP_1_2, $options->getVersion());
    }

    public function testCompression()
    {
        $options = (new SoapOptions)->setCompression(SOAP_COMPRESSION_ACCEPT);

        self::assertSame(SOAP_COMPRESSION_ACCEPT, $options->getCompression());
    }

    public function testKeepAlive()
    {
        $options = (new SoapOptions)->setKeepAlive(true);

        self::assertTrue($options->getKeepAlive());
        self::assertFalse($options->setKeepAlive(false)->getKeepAlive());
    }

    public function testExtractSoapClientOptions()
    {
        $options = new SoapOptions;
        self::assertSame([], $options->extractSoapClientOptions());

        $options
            ->setParameters($params = ['foo', 'bar'])
            ->setVersion(SOAP_1_1)
            ->setCompression(SOAP_COMPRESSION_DEFLATE)
            ->setKeepAlive(false);

        self::assertSame([
            'soap_version' => SOAP_1_1,
            'compression' => SOAP_COMPRESSION_DEFLATE,
            'keep_alive' => false,
        ], $options->extractSoapClientOptions());
    }
}
