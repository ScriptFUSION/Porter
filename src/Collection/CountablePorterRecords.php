<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\ImportSpecification;

class CountablePorterRecords extends PorterRecords implements \Countable
{
    use CountableRecordsTrait;

    /**
     * @param RecordCollection $records
     * @param int $count
     * @param ImportSpecification $specification
     */
    public function __construct(RecordCollection $records, $count, ImportSpecification $specification)
    {
        parent::__construct($records, $specification);

        $this->setCount($count);
    }
}
