<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Async\Throttle\Throttle;

/**
 * Specifies a connector that is rate-limited by a connection throttle.
 */
interface ThrottledConnector
{
    /**
     * Gets the connection throttle, invoked each time the connector fetches data.
     *
     * @return Throttle Connection throttle.
     */
    public function getThrottle(): Throttle;
}
