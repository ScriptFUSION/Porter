<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider\Resource;

use ScriptFUSION\Porter\Connector\ImportConnector;

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
    public function getProviderClassName(): string;

    /**
     * Fetches data from the provider using the the specified connector and presents it as an iterable series.
     *
     * @param ImportConnector $connector Connector.
     *
     * @return \Iterator Iterable data series.
     */
    public function fetch(ImportConnector $connector): \Iterator;
}
