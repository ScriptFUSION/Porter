<?php
namespace ScriptFUSION\Porter\Collection;

/**
 * Encapsulates an enumerable collection of records.
 */
abstract class RecordCollection implements \Iterator
{
    private $records;

    private $previousCollection;

    public function __construct(\Iterator $records, RecordCollection $previousCollection = null)
    {
        $this->records = $records;
        $this->previousCollection = $previousCollection;
    }

    public function current()
    {
        return $this->records->current();
    }

    public function next()
    {
        $this->records->next();
    }

    public function key()
    {
        return $this->records->key();
    }

    public function valid()
    {
        return $this->records->valid();
    }

    public function rewind()
    {
        $this->records->rewind();
    }

    /**
     * @return RecordCollection|null
     */
    public function getPreviousCollection()
    {
        return $this->previousCollection;
    }

    public function findFirstCollection()
    {
        do {
            $previous = isset($nextPrevious) ? $nextPrevious : $this->getPreviousCollection();
        } while ($previous && $nextPrevious = $previous->getPreviousCollection());

        return $previous ?: $this;
    }
}
