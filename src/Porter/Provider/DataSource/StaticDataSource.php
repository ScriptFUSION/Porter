<?php
namespace ScriptFUSION\Porter\Provider\DataSource;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Provider\StaticDataProvider;

class StaticDataSource implements ProviderDataSource
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

    public function fetch(Connector $connector, EncapsulatedOptions $options = null)
    {
        return $this->data;
    }
}
