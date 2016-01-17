<?php
namespace ScriptFUSION\Porter\Collection;

/**
 * Encapsulates an enumerable collection of records.
 */
abstract class RecordCollection implements \IteratorAggregate
{
    private $records;

    private $previousCollection;

    public function __construct(\Traversable $records, RecordCollection $previousCollection = null)
    {
        $this->records = $records;
        $this->previousCollection = $previousCollection;
    }

    public function getIterator()
    {
        return $this->records;
    }

    /**
     * @return RecordCollection
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
