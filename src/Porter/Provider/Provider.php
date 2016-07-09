<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;

/**
 * Provides a method for fetching data from a data source.
 */
interface Provider
{
    /**
     * Fetches data from the specified data source.
     *
     * @param ProviderDataSource $dataSource Data source.
     *
     * @return \Iterator Enumerable data series.
     */
    public function fetch(ProviderDataSource $dataSource);
}
