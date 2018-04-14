<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider\Resource;

use Amp\Iterator;
use ScriptFUSION\Porter\Connector\ImportConnector;

interface AsyncResource
{
    /**
     * Gets the class name of the provider this resource belongs to.
     *
     * @return string Provider class name.
     */
    public function getProviderClassName(): string;

    /**
     * Fetches data from the provider using the the specified connector and presents its data as an enumerable series.
     *
     * @param ImportConnector $connector Connector.
     *
     * @return Iterator Enumerable data series.
     */
    public function fetchAsync(ImportConnector $connector): Iterator;
}
