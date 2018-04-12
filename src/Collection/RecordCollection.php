<?php
namespace ScriptFUSION\Porter\Collection;

/**
 * Encapsulates an enumerable collection of records.
 */
abstract class RecordCollection implements \Iterator
{
    private $records;

    private $previousCollection;

    public function __construct(\Iterator $records, self $previousCollection = null)
    {
        $this->records = $records;
        $this->previousCollection = $previousCollection;
    }

    public function current(): array
    {
        return $this->records->current();
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->records->next();
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->records->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->records->valid();
    }

    /**
     * @return void
     */
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
