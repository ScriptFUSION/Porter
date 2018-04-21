<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Provides a method for fetching data from a remote source asynchronously.
 */
interface AsyncConnector
{
    /**
     * Fetches data from the specified source.
     *
     * @param ConnectionContext $context Runtime connection settings and methods.
     * @param string $source Source.
     *
     * @return \Closure Closure that returns a Promise or raw data.
     */
    public function fetchAsync(ConnectionContext $context, string $source): \Closure;
}
