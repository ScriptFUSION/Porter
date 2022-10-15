<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\ImportSpecification;

class PorterRecords extends RecordCollection
{
    private ImportSpecification $specification;

    public function __construct(RecordCollection $records, ImportSpecification $specification)
    {
        parent::__construct($records, $records);

        $this->specification = $specification;
    }

    public function getSpecification(): ImportSpecification
    {
        return $this->specification;
    }
}
