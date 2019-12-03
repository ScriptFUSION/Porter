<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use Amp\Promise;

/**
 * Specifies a data source and the necessary parameters required to fetch it asynchronously.
 */
interface AsyncDataSource
{
    /**
     * Computes the hash code for this object's state. The computed hash must be the same when the object state
     * is unchanged or is still considered equivalent for cache key purposes, otherwise the hash code must always
     * be different for different states.
     *
     * @return Promise<string> Hash code.
     */
    public function computeHash(): Promise;
}
