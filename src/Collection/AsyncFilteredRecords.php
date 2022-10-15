<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

class AsyncFilteredRecords extends AsyncRecordCollection
{
    private $filter;

    public function __construct(\Iterator $records, AsyncRecordCollection $previousCollection, callable $filter)
    {
        parent::__construct($records, $previousCollection);

        $this->filter = $filter;
    }

    public function getFilter(): callable
    {
        return $this->filter;
    }
}
