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
     *
     * @return mixed Async generator function or any return value compatible with Amp\call.
     */
    public function fetchAsync(string $source);
}
