<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

/**
 * Specifies runtime connection options.
 */
final class ConnectionContext
{
    private $mustCache;

    public function __construct(bool $mustCache)
    {
        $this->mustCache = $mustCache;
    }

    /**
     * Gets a value indicating whether the response for this request must be cached.
     *
     * @return bool True if the response must be cached, otherwise false.
     */
    public function mustCache(): bool
    {
        return $this->mustCache;
    }
}
