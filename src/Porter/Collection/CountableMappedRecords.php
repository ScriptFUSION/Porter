<?php
namespace ScriptFUSION\Porter\Collection;

class CountableMappedRecords extends MappedRecords implements \Countable
{
    private $count;

    /**
     * @param \Iterator $records
     * @param int $count
     * @param RecordCollection $previousCollection
     */
    public function __construct(\Iterator $records, $count, RecordCollection $previousCollection)
    {
        parent::__construct($records, $previousCollection);

        $this->count = $count|0;
    }

    public function count()
    {
        return $this->count;
    }
}
