<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Caching;

use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

class CachingTestConnector extends CachingConnector
{
    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        return 'bar';
    }
}