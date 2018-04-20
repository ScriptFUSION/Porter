<?php
declare(strict_types=1);

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

    public function next(): void
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

    public function valid(): bool
    {
        return $this->records->valid();
    }

    public function rewind(): void
    {
        $this->records->rewind();
    }

    public function getPreviousCollection(): ?RecordCollection
    {
        return $this->previousCollection;
    }

    public function findFirstCollection(): ?RecordCollection
    {
        do {
            $previous = $nextPrevious ?? $this->getPreviousCollection();
        } while ($previous && $nextPrevious = $previous->getPreviousCollection());

        return $previous ?: $this;
    }
}
