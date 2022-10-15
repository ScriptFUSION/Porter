<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Designates a connector decorator object and provides a method to access the wrapped connector.
 */
interface ConnectorWrapper
{
    /**
     * Gets the wrapped connector.
     */
    public function getWrappedConnector(): Connector|AsyncConnector;
}
