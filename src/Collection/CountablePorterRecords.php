<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\Specification;

class CountablePorterRecords extends PorterRecords implements \Countable
{
    use CountableRecordsTrait;

    public function __construct(RecordCollection $records, int $count, Specification $specification)
    {
        parent::__construct($records, $specification);

        $this->setCount($count);
    }
}
