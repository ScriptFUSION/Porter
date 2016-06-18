<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Http;

use ScriptFUSION\Porter\Net\Http\HttpOptions;

final class HttpOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testOptionDefaults()
    {
        $options = new HttpOptions;

        self::assertNull($options->getBaseUrl());
        self::assertSame([], $options->getQueryParameters());
        self::assertSame([], $options->getHeaders());
    }

    public function testFindHeader()
    {
        $options = (new HttpOptions)->addHeader('Foo: bar')->addHeader($baz = 'Baz: bat');

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

    public function testExtractHttpContextOptions()
    {
        self::assertSame([], (new HttpOptions)->extractHttpContextOptions());

        $options = (new HttpOptions)
            ->addHeader('foo')
            ->setBaseUrl('bar') // Not a context option.
        ;

        self::assertSame(['header' => ['foo']], $options->extractHttpContextOptions());

        // Ensure non-context options have not been altered.
        self::assertSame('bar', $options->getBaseUrl());
    }
}
