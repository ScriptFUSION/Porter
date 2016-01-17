<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderDataType;

class ProviderRecords extends RecordCollection
{
    private $dataType;

    public function __construct(\Traversable $providerRecords, ProviderDataType $dataType)
    {
        parent::__construct($providerRecords);

        $this->dataType = $dataType;
    }
}
