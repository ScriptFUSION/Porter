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
     * @param string $source Source.
     * @param ConnectionContext $context Runtime connection settings and methods.
     *
     * @return \Closure Closure that returns a Promise or raw data.
     */
    public function fetchAsync(string $source, ConnectionContext $context): \Closure;
}
