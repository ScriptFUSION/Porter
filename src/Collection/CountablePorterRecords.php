<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\ImportSpecification;

class CountablePorterRecords extends PorterRecords implements \Countable
{
    use CountableRecordsTrait;

    public function __construct(RecordCollection $records, int $count, ImportSpecification $specification)
    {
        parent::__construct($records, $specification);

        $this->setCount($count);
    }
}
