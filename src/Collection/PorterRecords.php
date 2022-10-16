<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\ImportSpecification;

class PorterRecords extends RecordCollection
{
    public function __construct(RecordCollection $records, private readonly ImportSpecification $specification)
    {
        parent::__construct($records, $records);

        // Force generators to run to first suspension point.
        $records->valid();
    }

    public function getSpecification(): ImportSpecification
    {
        return $this->specification;
    }
}
