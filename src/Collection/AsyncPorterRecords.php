<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\AsyncImportSpecification;

class AsyncPorterRecords extends AsyncRecordCollection
{
    public function __construct(
        AsyncRecordCollection $records,
        private readonly AsyncImportSpecification $specification
    ) {
        parent::__construct($records, $records);

        // Force generators to run to first suspension point.
        $records->valid();
    }

    public function getSpecification(): AsyncImportSpecification
    {
        return $this->specification;
    }
}
