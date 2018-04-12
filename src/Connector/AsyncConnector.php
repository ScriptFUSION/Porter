<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;

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
     * @return Promise Data.
     */
    public function fetchAsync(ConnectionContext $context, string $source): Promise;
}
