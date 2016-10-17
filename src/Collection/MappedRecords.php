<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Mapper\Mapping;

class MappedRecords extends RecordCollection
{
    /** @var Mapping */
    private $mapping;

    public function __construct(\Iterator $records, RecordCollection $previousCollection, Mapping $mapping)
    {
        parent::__construct($records, $previousCollection);

        $this->mapping = $mapping;
    }

    /**
     * @return Mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }
}
