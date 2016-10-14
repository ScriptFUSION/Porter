<?php
namespace ScriptFUSIONTest\Porter\Options;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

class TestOptions extends EncapsulatedOptions
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
}
