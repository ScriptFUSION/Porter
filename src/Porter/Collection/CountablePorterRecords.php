<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\ImportSpecification;

class CountablePorterRecords extends PorterRecords implements \Countable
{
    private $count;

    /**
     * @param RecordCollection $records
     * @param int $count
     * @param ImportSpecification $specification
     */
    public function __construct(RecordCollection $records, $count, ImportSpecification $specification)
    {
        parent::__construct($records, $specification);

        $this->count = $count|0;
    }

    public function count()
    {
        return $this->count;
    }
}
