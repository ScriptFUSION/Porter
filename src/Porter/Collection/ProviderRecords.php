<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;

class ProviderRecords extends RecordCollection
{
    private $dataSource;

    public function __construct(\Iterator $providerRecords, ProviderDataSource $dataSource)
    {
        parent::__construct($providerRecords);

        $this->dataSource = $dataSource;
    }

    /**
     * @return ProviderDataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
}
