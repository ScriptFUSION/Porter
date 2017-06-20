<?php
namespace ScriptFUSIONTest\Stubs;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

final class TestOptions extends EncapsulatedOptions
{
    public function __construct()
    {
        $this->setDefaults(['foo' => 'foo']);
    }

    public function setFoo($foo)
    {
        return $this->set('foo', $foo);
    }

    public function getFoo()
    {
        return $this->get('foo');
    }

    public function removeFooKey($child)
    {
        unset($this->getReference('foo')[$child]);
    }

    public function getBar()
    {
        return $this->get('bar');
    }
}
