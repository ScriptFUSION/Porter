<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\AsyncImportSpecification;

class CountableAsyncPorterRecords extends AsyncPorterRecords implements \Countable
{
    use CountableRecordsTrait;

    public function __construct(AsyncRecordCollection $records, int $count, AsyncImportSpecification $specification)
    {
        parent::__construct($records, $specification);

        $this->setCount($count);
    }
}
