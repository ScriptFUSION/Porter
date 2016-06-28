<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Provides a method for fetching data from a remote source.
 */
interface Connector
{
    /**
     * Fetches data from the specified source optionally augmented by the
     * specified options.
     *
     * @param string $source Source.
     * @param EncapsulatedOptions $options Optional. Options.
     *
     * @return mixed Data.
     */
    public function fetch($source, EncapsulatedOptions $options = null);
}
