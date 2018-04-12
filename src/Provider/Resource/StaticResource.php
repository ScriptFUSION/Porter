<?php
namespace ScriptFUSION\Porter\Provider\Resource;

use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Provider\StaticDataProvider;

class StaticResource implements ProviderResource
{
    private $data;

    public function __construct(\Iterator $data)
    {
        $this->data = $data;
    }

    public function getProviderClassName(): string
    {
        return StaticDataProvider::class;
    }

    public function fetch(ImportConnector $connector, EncapsulatedOptions $options = null): \Iterator
    {
        return $this->data;
    }
}
