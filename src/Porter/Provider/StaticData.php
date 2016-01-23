<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

class StaticData implements ProviderData
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

    public function fetch(Connector $connector)
    {
        return $this->data;
    }
}
