<?php
namespace ScriptFUSION\Porter\Provider;

class GenericProviderName implements ProviderName
{
    private $name;

    public function __construct($name)
    {
        $this->name = "$name";
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->getName()}";
    }
}
