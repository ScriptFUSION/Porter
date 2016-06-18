<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\ProviderDataFetcher;

class ProviderRecords extends RecordCollection
{
    private $dataFetcher;

    public function __construct(\Iterator $providerRecords, ProviderDataFetcher $dataFetcher)
    {
        parent::__construct($providerRecords);

        $this->dataFetcher = $dataFetcher;
    }

    /**
     * @return ProviderDataFetcher
     */
    public function getDataFetcher()
    {
        return $this->dataFetcher;
    }
}
