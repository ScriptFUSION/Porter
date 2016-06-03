<?php
namespace ScriptFUSION\Porter\Mapper;

use ScriptFUSION\Mapper\CollectionMapper;
use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAware;

class PorterMapper extends CollectionMapper
{
    private $porter;

    public function __construct(Porter $porter)
    {
        $this->porter = $porter;
    }

    public function mapRecords(RecordCollection $records, Mapping $mapping, $context = null)
    {
        return new MappedRecords($this->mapCollection($records, $mapping, $context), $records);
    }

    protected function injectDependencies($object)
    {
        parent::injectDependencies($object);

        if ($object instanceof PorterAware) {
            $object->setPorter($this->porter);
        }
    }
}
