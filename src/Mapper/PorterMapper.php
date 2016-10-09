<?php
namespace ScriptFUSION\Porter\Mapper;

use ScriptFUSION\Mapper\CollectionMapper;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAware;

class PorterMapper extends CollectionMapper
{
    private $porter;

    public function __construct(Porter $porter)
    {
        $this->porter = $porter;
    }

    protected function injectDependencies($object)
    {
        parent::injectDependencies($object);

        if ($object instanceof PorterAware) {
            $object->setPorter($this->porter);
        }
    }
}
