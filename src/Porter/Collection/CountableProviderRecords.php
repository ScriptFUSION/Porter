<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderDataType;

class CountableProviderRecords extends ProviderRecords implements \Countable
{
    private $count;

    /**
     * @param \Iterator $providerRecords
     * @param int $count
     * @param ProviderDataType $providerDataType
     */
    public function __construct(\Iterator $providerRecords, $count, ProviderDataType $providerDataType)
    {
        parent::__construct($providerRecords, $providerDataType);

        $this->count = $count|0;
    }

    public function count()
    {
        return $this->count;
    }
}
