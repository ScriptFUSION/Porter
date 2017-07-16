<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Cache\CacheAdvice;

final class ConnectionContext
{
    private $cacheAdvice;

    private $fetchExceptionHandler;

    private $maxFetchAttempts;

    public function __construct(CacheAdvice $cacheAdvice, callable $fetchExceptionHandler, $maxFetchAttempts)
    {
        $this->cacheAdvice = $cacheAdvice;
        $this->fetchExceptionHandler = $fetchExceptionHandler;
        $this->maxFetchAttempts = (int)$maxFetchAttempts;
    }

    public function retry(callable $callable)
    {
        return \ScriptFUSION\Retry\retry(
            $this->maxFetchAttempts,
            $callable,
            function (\Exception $exception) {
                // Throw exception if unrecoverable.
                if (!$exception instanceof RecoverableConnectorException) {
                    throw $exception;
                }

                call_user_func($this->fetchExceptionHandler, $exception);
            }
        );
    }

    /**
     * Gets a value indicating whether a connector should cache data.
     *
     * @return bool True if the connector should cache data, otherwise false.
     */
    public function shouldCache()
    {
        return $this->cacheAdvice->anyOf(CacheAdvice::SHOULD_CACHE(), CacheAdvice::MUST_CACHE());
    }

    /**
     * Gets a value indicating whether a connector must support caching.
     *
     * @return bool True if connector must support caching, otherwise false.
     */
    public function mustSupportCaching()
    {
        return $this->cacheAdvice->anyOf(CacheAdvice::MUST_CACHE(), CacheAdvice::MUST_NOT_CACHE());
    }
}
