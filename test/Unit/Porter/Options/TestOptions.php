<?php
namespace ScriptFUSIONTest\Unit\Porter\Options;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

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
