<?php
namespace ScriptFUSION\Porter\Collection;

use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;

class CountableProviderRecords extends ProviderRecords implements \Countable
{
    use CountableRecordsTrait;

    /**
     * @param \Iterator $providerRecords
     * @param int $count
     * @param ProviderDataSource $dataSource
     */
    public function __construct(\Iterator $providerRecords, $count, ProviderDataSource $dataSource)
    {
        parent::__construct($providerRecords, $dataSource);

        $this->setCount($count);
    }
}
