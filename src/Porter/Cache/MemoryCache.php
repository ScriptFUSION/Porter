<?php
namespace ScriptFUSION\Porter\Cache;

class MemoryCache extends \ArrayObject
{
    private $enabled = true;

    public function has($key)
    {
        return isset($this[$key]);
    }

    public function get($key)
    {
        return $this[$key];
    }

    public function set($key, $value)
    {
        return $this[$key] = $value;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function offsetExists($index)
    {
        $this->ensureOperationPermitted('read');

        return parent::offsetExists($index);
    }

    public function offsetGet($index)
    {
        $this->ensureOperationPermitted('read');

        return parent::offsetGet($index);
    }

    public function offsetSet($index, $value)
    {
        $this->ensureOperationPermitted('write');

        parent::offsetSet($index, $value);
    }

    public function offsetUnset($index)
    {
        $this->ensureOperationPermitted('write');

        parent::offsetUnset($index);
    }

    private function ensureOperationPermitted($operation)
    {
        if (!$this->isEnabled()) {
            throw new CacheOperationProhibitedException("Cannot $operation: cache is disabled.");
        }
    }
}
