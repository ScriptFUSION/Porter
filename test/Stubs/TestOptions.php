<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Stubs;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

final class TestOptions extends EncapsulatedOptions
{
    public function __construct()
    {
        $this->setDefaults(['foo' => 'foo']);
    }

    public function setFoo($foo): self
    {
        return $this->set('foo', $foo);
    }

    /**
     * @return mixed
     */
    public function getFoo()
    {
        return $this->get('foo');
    }

    public function removeFooKey($child): void
    {
        unset($this->getReference('foo')[$child]);
    }

    /**
     * @return mixed
     */
    public function getBar()
    {
        return $this->get('bar');
    }
}
