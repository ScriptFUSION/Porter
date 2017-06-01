<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Http;

use ScriptFUSION\Porter\Net\Http\HttpOptions;

final class HttpOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testOptionDefaults()
    {
        $options = new HttpOptions;

        self::assertSame([], $options->getQueryParameters());
        self::assertSame([], $options->getHeaders());
    }

    public function testQueryParameters()
    {
        self::assertSame(
            $query = ['foo' => 'bar'],
            (new HttpOptions)->setQueryParameters($query)->getQueryParameters()
        );
    }

    public function testMethod()
    {
        self::assertSame('foo', (new HttpOptions)->setMethod('foo')->getMethod());
    }

    public function testFindHeader()
    {
        $options = (new HttpOptions)->addHeader('Foo: bar')->addHeader($baz = 'Baz: qux');

        self::assertNull($options->findHeader('baz'));
        self::assertSame($baz, $options->findHeader('Baz'));
    }

    public function testFindHeaders()
    {
        $options = (new HttpOptions)
            ->addHeader($foo1 = 'Foo: bar')
            ->addHeader($foo2 = 'Foo: baz')
            ->addHeader('Qux: Quux')
        ;

        self::assertSame([$foo1, $foo2], $options->findHeaders('Foo'));
    }

    public function testReplaceHeaders()
    {
        $options = (new HttpOptions)
            ->addHeader('Foo: bar')
            ->addHeader('Foo: baz')
            ->addHeader($qux = 'Qux: Quux')
        ;

        $options->replaceHeaders('Foo', $foo = 'Foo: corge');

        self::assertContains($qux, $options->getHeaders());
        self::assertContains($foo, $options->getHeaders());
        self::assertCount(2, $options->getHeaders());
    }

    public function testProxy()
    {
        self::assertSame($host = 'http://example.com', (new HttpOptions)->setProxy($host)->getProxy());
    }

    public function testUserAgent()
    {
        self::assertSame($userAgent = 'Foo/Bar', (new HttpOptions)->setUserAgent($userAgent)->getUserAgent());
    }

    public function testFollowLocation()
    {
        $options = new HttpOptions;

        self::assertTrue($options->setFollowLocation(true)->getFollowLocation());
        self::assertFalse($options->setFollowLocation(false)->getFollowLocation());
    }

    public function testRequestFullUri()
    {
        $options = new HttpOptions;

        self::assertTrue($options->setRequestFullUri(true)->getRequestFullUri());
        self::assertFalse($options->setRequestFullUri(false)->getRequestFullUri());
    }

    public function testMaxRedirects()
    {
        self::assertSame($maxRedirects = 10, (new HttpOptions)->setMaxRedirects($maxRedirects)->getMaxRedirects());
    }

    public function testProtocolVersion()
    {
        self::assertSame(
            $protocolVersion = 1.1,
            (new HttpOptions)->setProtocolVersion($protocolVersion)->getProtocolVersion()
        );
    }

    public function testTimeout()
    {
        self::assertSame($timeout = 20.0, (new HttpOptions)->setTimeout($timeout)->getTimeout());
    }

    public function testIgnoreErrors()
    {
        self::assertTrue((new HttpOptions)->setIgnoreErrors(true)->getIgnoreErrors());
    }

    public function testExtractHttpContextOptions()
    {
        self::assertSame(['header' => []], (new HttpOptions)->extractHttpContextOptions());

        $options = (new HttpOptions)->addHeader('foo');

        self::assertSame(['header' => ['foo']], $options->extractHttpContextOptions());
    }

    public function testContent()
    {
        self::assertSame($content = "foo\nbar", (new HttpOptions)->setContent($content)->getContent());
    }
}
