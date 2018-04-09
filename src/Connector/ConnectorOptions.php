<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

interface ConnectorOptions
{
    /**
     * @return EncapsulatedOptions
     */
    public function getOptions();
}
