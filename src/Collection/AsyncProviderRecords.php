<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\Resource\AsyncResource;

class AsyncProviderRecords extends AsyncRecordCollection
{
    public function __construct(\Iterator $records, private readonly AsyncResource $resource)
    {
        parent::__construct($records);
    }

    public function getResource(): AsyncResource
    {
        return $this->resource;
    }
}
