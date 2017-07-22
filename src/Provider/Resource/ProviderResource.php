<?php
namespace ScriptFUSION\Porter\Provider\Resource;

use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Defines methods for fetching data from a specific provider resource.
 */
interface ProviderResource
{
    /**
     * Gets the class name of the provider this resource belongs to.
     *
     * @return string Provider class name.
     */
    public function getProviderClassName();

    /**
     * Fetches data from the provider using the the specified connector and presents its data as an enumerable series.
     *
     * @param ImportConnector $connector Connector.
     * @param EncapsulatedOptions $options Optional. Options.
     *
     * @return \Iterator Enumerable data series.
     */
    public function fetch(ImportConnector $connector, EncapsulatedOptions $options = null);
}
