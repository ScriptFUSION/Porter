<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Import\Import;

class CountablePorterRecords extends PorterRecords implements \Countable
{
    use CountableRecordsTrait;

    public function __construct(RecordCollection $records, int $count, Import $import)
    {
        parent::__construct($records, $import);

        $this->setCount($count);
    }
}
