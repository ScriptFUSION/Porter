<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Defines methods for fetching data from a specific provider.
 */
interface ProviderDataFetcher
{
    /**
     * Gets the class name of the provider this data fetcher belongs to.
     *
     * @return string
     */
    public function getProviderName();

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
