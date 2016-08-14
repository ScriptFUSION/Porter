<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

class ProviderRecords extends RecordCollection
{
    private $resource;

    public function __construct(\Iterator $providerRecords, ProviderResource $resource)
    {
        parent::__construct($providerRecords);

        $this->resource = $resource;
    }

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
