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
     * Gets a connector for accessing resource data.
     */
    public function getAsyncConnector(): AsyncConnector;
}
