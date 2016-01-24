<?php
namespace ScriptFUSION\Porter\Mapping;

use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;

class Mapper
{
    private $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function map(RecordCollection $records, Mapping $mapping, $context = null)
    {
        $map = function () use ($records, $mapping, $context) {
            foreach ($records as $record) {
                yield $this->resolver->resolveMapping($mapping, $record, $context);
            }
        };

        return new MappedRecords($map(), $records);
    }
}
