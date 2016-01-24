<?php
namespace ScriptFUSIONTest\Unit\Porter\Options;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

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
        $this->assertSame('foo', $this->options->getFoo());
    }

    public function testSet()
    {
        $this->assertSame($this->options, $this->options->setFoo('bar'));

        $this->assertSame('bar', $this->options->getFoo());
    }

    public function testCopy()
    {
        $this->assertSame([], $this->options->copy());

        $this->options->setFoo('bar');

        $this->assertSame(['foo' => 'bar'], $this->options->copy());
    }

    public function testGetReference()
    {
        $this->options->setFoo(['bar' => 'bar', 'baz' => 'baz']);

        $this->options->removeFooKey('bar');

        $this->assertSame(['baz' => 'baz'], $this->options->getFoo());
    }
}

final class TestOptions extends EncapsulatedOptions
{
    public function setFoo($foo)
    {
        return $this->set('foo', $foo);
    }

    public function getFoo()
    {
        return $this->get('foo', 'foo');
    }

    public function removeFooKey($child)
    {
        unset($this->getReference('foo')[$child]);
    }
}
