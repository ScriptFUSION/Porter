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

    public function testLocation()
    {
        self::assertSame($location = 'http://foo.com', (new SoapOptions)->setLocation($location)->getLocation());
    }

    public function testUri()
    {
        self::assertSame($uri = 'http://foo.com', (new SoapOptions)->setUri($uri)->getUri());
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

    public function testProxy()
    {
        $options = (new SoapOptions)
            ->setProxyHost($host = 'foo.com')
            ->setProxyPort($port = 8080)
            ->setProxyLogin($login = 'foo')
            ->setProxyPassword($password = 'bar');

        self::assertSame($host, $options->getProxyHost());
        self::assertSame($port, $options->getProxyPort());
        self::assertSame($login, $options->getProxyLogin());
        self::assertSame($password, $options->getProxyPassword());
    }

    public function testEncoding()
    {
        self::assertSame($encoding = 'iso-8859-1', (new SoapOptions)->setEncoding($encoding)->getEncoding());
    }

    public function testTrace()
    {
        $options = (new SoapOptions)->setTrace(true);

        self::assertTrue($options->getTrace());
        self::assertFalse($options->setTrace(false)->getTrace());
    }

    public function testExceptions()
    {
        $options = (new SoapOptions)->setExceptions(true);

        self::assertTrue($options->getExceptions());
        self::assertFalse($options->setExceptions(false)->getExceptions());
    }

    public function testClassmap()
    {
        $classmap = ['foo' => 'bar'];

        self::assertSame($classmap, (new SoapOptions)->setClassmap($classmap)->getClassmap());
    }

    public function testConnectionTimeout()
    {
        self::assertSame($timeout = 45, (new SoapOptions)->setConnectionTimeout($timeout)->getConnectionTimeout());
    }

    public function testTypemap()
    {
        $typemap = ['foo' => 'bar'];
        self::assertSame($typemap, (new SoapOptions)->setTypemap($typemap)->getTypemap());
    }

    public function testCacheWsdl()
    {
        self::assertSame($option = WSDL_CACHE_MEMORY, (new SoapOptions)->setCacheWsdl($option)->getCacheWsdl());
    }

    public function testUserAgent()
    {
        self::assertSame($userAgent = 'Foo/Bar', (new SoapOptions)->setUserAgent($userAgent)->getUserAgent());
    }

    public function testFeatures()
    {
        self::assertSame($features = SOAP_USE_XSI_ARRAY_TYPE, (new SoapOptions)->setFeatures($features)->getFeatures());
    }

    public function testSslMethod()
    {
        self::assertSame($method = SOAP_SSL_METHOD_SSLv2, (new SoapOptions)->setSslMethod($method)->getSslMethod());
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
