<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

class ProviderRecords extends RecordCollection
{
    private ProviderResource $resource;

    public function __construct(\Iterator $providerRecords, ProviderResource $resource)
    {
        parent::__construct($providerRecords);

        $this->resource = $resource;
    }

    public function getResource(): ProviderResource
    {
        return $this->resource;
    }
}
