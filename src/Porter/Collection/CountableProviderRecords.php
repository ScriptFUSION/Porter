<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderDataFetcher;

class CountableProviderRecords extends ProviderRecords implements \Countable
{
    private $count;

    /**
     * @param \Iterator $providerRecords
     * @param int $count
     * @param ProviderDataFetcher $dataFetcher
     */
    public function __construct(\Iterator $providerRecords, $count, ProviderDataFetcher $dataFetcher)
    {
        parent::__construct($providerRecords, $dataFetcher);

        $this->count = $count|0;
    }

    public function count()
    {
        return $this->count;
    }
}
