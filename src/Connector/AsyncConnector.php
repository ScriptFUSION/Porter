<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;

/**
 * Provides a method for fetching data from a source asynchronously.
 */
interface AsyncConnector
{
    /**
     * Fetches data from the specified source.
     *
     * @param DataSource $source Source.
     *
     * @return Promise<mixed> Data.
     */
    public function fetchAsync(DataSource $source): Promise;
}
