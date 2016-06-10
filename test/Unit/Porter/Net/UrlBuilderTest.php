<?php
namespace ScriptFUSIONTest\Unit\Porter\Net;

use ScriptFUSION\Porter\Connector\Http\HttpOptions;
use ScriptFUSION\Porter\Net\UrlBuilder;

final class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildQuery()
    {
        $builder = new UrlBuilder((new HttpOptions)->setQueryParameters(['foo' => 'tertiary']));
        $url = 'http://bar/baz?';

        self::assertSame($url . 'foo=primary', $builder->buildUrl($url . 'foo=secondary', ['foo' => 'primary']));
        self::assertSame($url . 'foo=secondary', $builder->buildUrl($url . 'foo=secondary'));
        self::assertSame($url . 'foo=tertiary', $builder->buildUrl($url));
    }
}
