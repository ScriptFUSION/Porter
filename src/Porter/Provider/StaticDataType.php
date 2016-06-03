<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

class StaticDataType implements ProviderDataType
{
    private $data;

    public function __construct(\Iterator $data)
    {
        $this->data = $data;
    }

    public function getProviderName()
    {
        return StaticDataProvider::class;
    }

    public function fetch(Connector $connector, EncapsulatedOptions $options = null)
    {
        return $this->data;
    }
}
