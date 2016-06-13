<?php
namespace ScriptFUSIONTest\Integration\Porter\Net;

use ScriptFUSION\Porter\Connector\Http\HttpOptions;
use ScriptFUSION\Porter\Net\UrlBuilder;

final class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that UrlBuilder correctly builds a full URL from the specified base
     * and relative URL fragments.
     *
     * @param string|null $base Base URL path fragment.
     * @param string|null $relative Relative URL path fragment.
     * @param string $full Full URL.
     *
     * @dataProvider providerUrlPathFragments
     */
    public function testBuildUrl($base, $relative, $full)
    {
        $builder = new UrlBuilder((new HttpOptions)->setBaseUrl("http://foo/$base"));

        self::assertSame("http://foo/$full", $builder->buildUrl("$relative"));
    }

    public function providerUrlPathFragments()
    {
        return [
            [null,   null,   null],
            ['bar',  null,  'bar'],
            [null,   'bar', 'bar'],
            ['bar',  'baz', 'baz'],
            ['bar/', 'baz', 'bar/baz'],
            ['bar', '/baz', 'baz'],
        ];
    }

    public function testBuildQuery()
    {
        $builder = new UrlBuilder((new HttpOptions)->setQueryParameters(['foo' => 'tertiary']));
        $url = 'http://bar/baz?';

        self::assertSame($url . 'foo=primary', $builder->buildUrl($url . 'foo=secondary', ['foo' => 'primary']));
        self::assertSame($url . 'foo=secondary', $builder->buildUrl($url . 'foo=secondary'));
        self::assertSame($url . 'foo=tertiary', $builder->buildUrl($url));
    }
}
