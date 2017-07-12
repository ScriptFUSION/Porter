<?php
namespace ScriptFUSION\Porter\Provider\Resource;

use ScriptFUSION\Porter\Connector\SuperConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Provider\StaticDataProvider;

class StaticResource implements ProviderResource
{
    private $data;

    public function __construct(\Iterator $data)
    {
        $this->data = $data;
    }

    public function getProviderClassName()
    {
        return StaticDataProvider::class;
    }

    public function fetch(SuperConnector $connector, EncapsulatedOptions $options = null)
    {
        return $this->data;
    }
}
