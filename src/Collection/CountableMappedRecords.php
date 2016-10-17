<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Mapper\Mapping;

class CountableMappedRecords extends MappedRecords implements \Countable
{
    use CountableRecordsTrait;

    /**
     * @param \Iterator $records
     * @param int $count
     * @param RecordCollection $previousCollection
     */
    public function __construct(\Iterator $records, $count, RecordCollection $previousCollection, Mapping $mapping)
    {
        parent::__construct($records, $previousCollection, $mapping);

        $this->setCount($count);
    }
}
