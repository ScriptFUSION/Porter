<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

class CountableProviderRecords extends ProviderRecords implements \Countable
{
    use CountableRecordsTrait;

    /**
     * @param \Iterator $providerRecords
     * @param int $count
     * @param ProviderResource $resource
     */
    public function __construct(\Iterator $providerRecords, int $count, ProviderResource $resource)
    {
        parent::__construct($providerRecords, $resource);

        $this->setCount($count);
    }
}
