<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderDataType;

class ProviderRecords extends RecordCollection
{
    private $providerData;

    public function __construct(\Iterator $providerRecords, ProviderDataType $providerDataType)
    {
        parent::__construct($providerRecords);

        $this->providerData = $providerDataType;
    }

    /**
     * @return ProviderDataType
     */
    public function getProviderData()
    {
        return $this->providerData;
    }
}
