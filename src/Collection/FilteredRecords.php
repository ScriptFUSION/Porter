<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

class FilteredRecords extends RecordCollection
{
    private $filter;

    public function __construct(\Iterator $records, RecordCollection $previousCollection, callable $filter)
    {
        parent::__construct($records, $previousCollection);

        $this->filter = $filter;
    }

    public function getFilter(): callable
    {
        return $this->filter;
    }
}
