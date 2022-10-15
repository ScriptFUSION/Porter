<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\AsyncImportSpecification;

class AsyncPorterRecords extends AsyncRecordCollection
{
    private AsyncImportSpecification $specification;

    public function __construct(AsyncRecordCollection $records, AsyncImportSpecification $specification)
    {
        parent::__construct($records, $records);

        $this->specification = $specification;
    }

    public function getSpecification(): AsyncImportSpecification
    {
        return $this->specification;
    }
}
