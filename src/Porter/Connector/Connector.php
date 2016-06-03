<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

interface Connector
{
    /**
     * @param string $source
     * @param EncapsulatedOptions $options
     *
     * @return mixed
     */
    public function fetch($source, EncapsulatedOptions $options = null);
}
