<?php
namespace ScriptFUSION\Porter\Connector;

/**
 * Provides a method for fetching data from a remote source.
 */
interface Connector
{
    /**
     * Fetches data from the specified source optionally augmented by the specified options.
     *
     * @param ConnectionContext $context Runtime connection settings and methods.
     * @param string $source Source.
     *
     * @return mixed Data.
     */
    public function fetch(ConnectionContext $context, $source);
}
