<?php
namespace ScriptFUSIONTest\Unit\Porter\Type;

use ScriptFUSION\Porter\Type\StringType;

final class StringTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testStartsWith()
    {
        self::assertTrue(StringType::startsWith('foo', 'f'));
        self::assertTrue(StringType::startsWith('foo', 'foo'));

        self::assertFalse(StringType::startsWith('foo', 'oo'));
        self::assertFalse(StringType::startsWith('f', 'foo'));
    }

    public function testEndsWith()
    {
        self::assertTrue(StringType::endsWith('foo', 'oo'));
        self::assertTrue(StringType::endsWith('foo', 'foo'));

        self::assertFalse(StringType::endsWith('foo', 'f'));
        self::assertFalse(StringType::endsWith('f', 'foo'));
    }
}
