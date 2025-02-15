<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Import\Import;

class PorterRecords extends RecordCollection
{
    public function __construct(RecordCollection $records, private readonly Import $import)
    {
        parent::__construct($records, $records);

        // Force generators to run to the first suspension point.
        $records->valid();
    }

    public function getImport(): Import
    {
        return $this->import;
    }
}
