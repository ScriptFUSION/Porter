<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderData;

class ProviderRecords extends RecordCollection
{
    private $providerData;

    public function __construct(\Traversable $providerRecords, ProviderData $providerData)
    {
        parent::__construct($providerRecords);

        $this->providerData = $providerData;
    }

    /**
     * @return ProviderData
     */
    public function getProviderData()
    {
        return $this->providerData;
    }
}
