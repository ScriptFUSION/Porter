<?php
namespace ScriptFUSION\Porter\Transform;

use ScriptFUSION\Porter\Collection\RecordCollection;

interface Transformer
{
    /**
     * @param RecordCollection $records
     * @param mixed $context
     *
     * @return RecordCollection
     */
    public function transform(RecordCollection $records, $context);
}
