<?php
namespace ScriptFUSION\Porter\Collection;

class CountableMappedRecords extends MappedRecords implements \Countable
{
    use CountableRecordsTrait;

    /**
     * @param \Iterator $records
     * @param int $count
     * @param RecordCollection $previousCollection
     */
    public function __construct(\Iterator $records, $count, RecordCollection $previousCollection)
    {
        parent::__construct($records, $previousCollection);

        $this->setCount($count);
    }
}
