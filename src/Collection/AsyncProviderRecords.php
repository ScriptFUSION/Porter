<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use Amp\Iterator;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;

class AsyncProviderRecords extends AsyncRecordCollection
{
    private $resource;

    public function __construct(Iterator $records, AsyncResource $resource)
    {
        parent::__construct($records);

        $this->resource = $resource;
    }

    public function getResource(): AsyncResource
    {
        return $this->resource;
    }
}
