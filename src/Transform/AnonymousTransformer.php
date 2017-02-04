<?php
namespace ScriptFUSION\Porter\Transform;

use ScriptFUSION\Porter\Collection\RecordCollection;

class AnonymousTransformer implements Transformer
{
    private $transformer;

    public function __construct(callable $transformer)
    {
        $this->transformer = $transformer;
    }

    public function transform(RecordCollection $records, $context)
    {
        return call_user_func($this->transformer, $records, $context);
    }
}
