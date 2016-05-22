<?php
namespace ScriptFUSIONTest\Unit\Porter\Options;

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
        self::assertSame([], $this->options->copy());

        $this->options->setFoo('bar');

        self::assertSame(['foo' => 'bar'], $this->options->copy());
    }

    public function testGetReference()
    {
        $this->options->setFoo(['bar' => 'bar', 'baz' => 'baz']);

        $this->options->removeFooKey('bar');

        self::assertSame(['baz' => 'baz'], $this->options->getFoo());
    }
}
