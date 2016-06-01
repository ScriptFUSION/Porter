<?php
namespace ScriptFUSION\Porter\Mapper;

use ScriptFUSION\Mapper\Mapper;
use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAware;

class PorterMapper extends Mapper
{
    private $porter;

    public function __construct(Porter $porter)
    {
        $this->porter = $porter;
    }

    public function mapRecords(RecordCollection $records, Mapping $mapping, $context = null)
    {
        $map = function () use ($records, $mapping, $context) {
            foreach ($records as $record) {
                yield $this->mapMapping($record, $mapping, $context);
            }
        };

        return new MappedRecords($map(), $records);
    }

    protected function injectDependencies($object)
    {
        parent::injectDependencies($object);

        if ($object instanceof PorterAware) {
            $object->setPorter($this->porter);
        }
    }
}
