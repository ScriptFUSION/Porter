<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\Resource\AsyncResource;

class CountableAsyncProviderRecords extends AsyncProviderRecords implements \Countable
{
    use CountableRecordsTrait;

    public function __construct(\Iterator $records, int $count, AsyncResource $resource)
    {
        parent::__construct($records, $resource);

        $this->setCount($count);
    }
}
