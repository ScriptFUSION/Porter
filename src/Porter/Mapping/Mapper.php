<?php
namespace ScriptFUSION\Porter\Mapping;

use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Porter;

class Mapper
{
    /** @var Resolver */
    private $resolver;

    public function __construct(Porter $porter)
    {
        $this->resolver = new Resolver($porter);
    }

    public function map(RecordCollection $documents, Mapping $mapping, $context = null, Porter $porter = null)
    {
        $map = function () use ($documents, $mapping, $context) {
            foreach ($documents as $document) {
                yield $this->resolver->resolveMapping($mapping, $document, $context);
            }
        };

        return new MappedRecords($map(), $documents);
    }
}
