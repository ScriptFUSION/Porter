<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Provides a method for fetching data from a data source asynchronously.
 */
interface AsyncConnector
{
    /**
     * Fetches data asynchronously from the specified data source.
     *
     * @param AsyncDataSource $source Data source.
     *
     * @return mixed Data.
     */
    public function fetchAsync(AsyncDataSource $source): mixed;
}
