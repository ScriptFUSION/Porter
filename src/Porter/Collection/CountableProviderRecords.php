<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderData;

class CountableProviderRecords extends ProviderRecords implements \Countable
{
    private $count;

    /**
     * @param \Traversable $providerRecords
     * @param int $count
     * @param ProviderData $providerData
     */
    public function __construct(\Traversable $providerRecords, $count, ProviderData $providerData)
    {
        parent::__construct($providerRecords, $providerData);

        $this->count = $count|0;
    }

    public function count()
    {
        return $this->count;
    }
}
