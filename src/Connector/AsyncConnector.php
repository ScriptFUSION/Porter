<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;

/**
 * Provides a method for fetching data from a data source asynchronously.
 */
interface AsyncConnector
{
    /**
     * Fetches data asynchronously from the specified data source.
     *
     * @param DataSource $source Data source.
     *
     * @return Promise<mixed> Data.
     */
    public function fetchAsync(DataSource $source): Promise;
}
