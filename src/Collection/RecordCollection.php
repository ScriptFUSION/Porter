<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

/**
 * Encapsulates an enumerable collection of records.
 */
abstract class RecordCollection implements \Iterator
{
    public function __construct(private readonly \Iterator $records, private readonly ?self $previousCollection = null)
    {
    }

    // TODO: Consider throwing our own exception type for clarity, instead of relying on PHP's TypeError.
    public function current(): array
    {
        return $this->records->current();
    }

    public function next(): void
    {
        $this->records->next();
    }

    public function key(): mixed
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

    public function getPreviousCollection(): ?self
    {
        return $this->previousCollection;
    }

    public function findFirstCollection(): ?self
    {
        do {
            $previous = $nextPrevious ?? $this->getPreviousCollection();
        } while ($previous && $nextPrevious = $previous->getPreviousCollection());

        return $previous ?: $this;
    }
}
