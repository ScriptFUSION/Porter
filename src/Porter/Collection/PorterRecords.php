<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Specification\ImportSpecification;

class PorterRecords extends RecordCollection
{
    private $specification;

    public function __construct(RecordCollection $records, ImportSpecification $specification)
    {
        parent::__construct($records, $records);

        $this->specification = $specification;
    }

    /**
     * @return ImportSpecification
     */
    public function getSpecification()
    {
        return $this->specification;
    }
}
