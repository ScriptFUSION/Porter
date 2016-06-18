<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\Connector;

/**
 * Specifies how to fetch data from the specified provider.
 */
interface ProviderDataFetcher
{
    /**
     * @return string
     */
    public function getProviderName();

    /**
     * Fetches data from the provider using the the specified connector and
     * presents the data as an iterative series.
     *
     * @param Connector $connector Connector.
     *
     * @return \Iterator Data series.
     */
    public function fetch(Connector $connector);
}
