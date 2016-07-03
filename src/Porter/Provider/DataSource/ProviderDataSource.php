<?php
namespace ScriptFUSION\Porter\Provider\DataSource;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Defines methods for fetching data from a specific provider data source.
 */
interface ProviderDataSource
{
    /**
     * Gets the class name of the provider this data source belongs to.
     *
     * @return string
     */
    public function getProviderClassName();

    /**
     * Fetches data from the provider using the the specified connector and
     * presents its data as an enumerable series.
     *
     * @param Connector $connector Connector.
     *
     * @return \Iterator Enumerable data series.
     */
    public function fetch(Connector $connector);
}
