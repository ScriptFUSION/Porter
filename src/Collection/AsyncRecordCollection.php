<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use Amp\Iterator;
use Amp\Promise;

abstract class AsyncRecordCollection implements Iterator
{
    private $records;

    private $previousCollection;

    public function __construct(Iterator $records, self $previousCollection = null)
    {
        $this->records = $records;
        $this->previousCollection = $previousCollection;
    }

    public function advance(): Promise
    {
        return $this->records->advance();
    }

    public function getCurrent(): array
    {
        return $this->records->getCurrent();
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
