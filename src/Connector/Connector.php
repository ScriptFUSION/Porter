<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Provides a method for fetching data from a remote source.
 */
interface Connector
{
    /**
     * Fetches data from the specified source optionally augmented by the specified options.
     *
     * @param string $source Source.
     * @param ConnectionContext $context Runtime connection settings and methods.
     *
     * @return mixed Data.
     */
    public function fetch(string $source, ConnectionContext $context);
}
