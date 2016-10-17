<?php
namespace ScriptFUSION\Porter\Collection;

class FilteredRecords extends RecordCollection
{
    /** @var callable */
    private $filter;

    public function __construct(\Iterator $records, RecordCollection $previousCollection, callable $filter)
    {
        parent::__construct($records, $previousCollection);

        $this->filter = $filter;
    }

    /**
     * @return callable
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
