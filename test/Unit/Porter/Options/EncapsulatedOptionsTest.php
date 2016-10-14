<?php
namespace ScriptFUSIONTest\Unit\Porter\Options;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Options\MergeException;
use ScriptFUSIONTest\Porter\Options\TestOptions;

final class EncapsulatedOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestOptions */
    private $options;

    protected function setUp()
    {
        $this->options = new TestOptions;
    }

    public function testGet()
    {
        self::assertSame('foo', $this->options->getFoo());
    }

    public function testSet()
    {
        self::assertSame($this->options, $this->options->setFoo('bar'));

        self::assertSame('bar', $this->options->getFoo());
    }

    public function testSetNullOverridesDefault()
    {
        $this->options->setFoo(null);

        self::assertNull($this->options->getFoo());
    }

    public function testCopy()
    {
        self::assertSame(['foo' => 'foo'], $this->options->copy());

        $this->options->setFoo('bar');

        self::assertSame(['foo' => 'bar'], $this->options->copy());
    }

    public function testMerge()
    {
        $a = $this->options;
        $b = (new TestOptions)->setFoo('bar');
        $c = clone $a;

        self::assertSame('foo', $a->getFoo());
        self::assertSame('bar', $b->getFoo());
        self::assertSame('foo', $c->getFoo());

        $c->merge($b);

        self::assertSame('foo', $a->getFoo());
        self::assertSame('bar', $b->getFoo());
        self::assertSame('bar', $c->getFoo());

        $c->merge($a);

        self::assertSame('foo', $a->getFoo());
        self::assertSame('bar', $b->getFoo());
        self::assertSame('foo', $c->getFoo());
    }

    public function testMergeDerivedClass()
    {
        $this->options->merge(\Mockery::mock(TestOptions::class));

        // PHPUnit asserts no exception is thrown.
    }

    public function testMergeNonDerivedClass()
    {
        $this->setExpectedException(MergeException::class, TestOptions::class);

        $this->options->merge(\Mockery::mock(EncapsulatedOptions::class));
    }

    public function testGetReference()
    {
        $this->options->setFoo(['bar' => 'bar', 'baz' => 'baz']);

        $this->options->removeFooKey('bar');

        self::assertSame(['baz' => 'baz'], $this->options->getFoo());
    }
}
