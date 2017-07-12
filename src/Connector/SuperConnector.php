<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Cache\Cache;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

final class SuperConnector
{
    private $connector;

    private $context;

    public function __construct(Connector $connector, ConnectionContext $context)
    {
        $this->connector = $connector;
        $this->context = $context;
    }

    public function fetch($source, EncapsulatedOptions $options = null)
    {
        $this->validateCacheState();

        return $this->connector->fetch($this->context, $source, $options);
    }

    private function validateCacheState()
    {
        if ($this->context->mustSupportCaching()) {
            if (!$this->connector instanceof Cache) {
                throw CacheUnavailableException::unsupported();
            }

            if (!$this->connector->isCacheAvailable()) {
                throw CacheUnavailableException::unavailable();
            }
        }
    }
}
