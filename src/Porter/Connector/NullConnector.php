<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

class NullConnector implements Connector
{
    public function fetch($source, EncapsulatedOptions $options = null)
    {
        // Intentionally empty.
    }
}
