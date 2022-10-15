<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Provides a method for fetching data from a data source.
 */
interface Connector
{
    /**
     * Fetches data from the specified data source.
     *
     * @param DataSource $source Data source.
     *
     * @return mixed Data.
     */
    public function fetch(DataSource $source): mixed;
}
