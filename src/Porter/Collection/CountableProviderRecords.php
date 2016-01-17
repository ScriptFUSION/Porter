<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderDataType;

class CountableProviderRecords extends ProviderRecords implements \Countable
{
    private $count;

    /**
     * @param \Traversable $providerRecords
     * @param int $count
     * @param ProviderDataType $dataType
     */
    public function __construct(\Traversable $providerRecords, $count, ProviderDataType $dataType)
    {
        parent::__construct($providerRecords, $dataType);

        $this->count = $count|0;
    }

    public function count()
    {
        return $this->count;
    }
}
