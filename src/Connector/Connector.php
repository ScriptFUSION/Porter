<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Provides a method for fetching data from a source.
 */
interface Connector
{
    /**
     * Fetches data from the specified source.
     *
     * @param DataSource $source Source.
     *
     * @return mixed Data.
     */
    public function fetch(DataSource $source);
}
