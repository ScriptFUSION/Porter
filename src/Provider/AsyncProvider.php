<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Connector\AsyncConnector;

/**
 * Provides a method for accessing an asynchronous connector.
 */
interface AsyncProvider
{
    /**
     * Gets an asynchronous connector compatible with this provider's resources.
     */
    public function getAsyncConnector(): AsyncConnector;
}
