<?php
namespace ScriptFUSIONTest;

use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class FixtureFactory
{
    use StaticClass;

    /**
     * Builds ConnectionContexts with sane defaults for testing.
     *
     * @param CacheAdvice $cacheAdvice
     * @param callable|null $fetchExceptionHandler
     * @param int $maxFetchAttempts
     *
     * @return ConnectionContext
     */
    public static function buildConnectionContext(
        CacheAdvice $cacheAdvice = null,
        callable $fetchExceptionHandler = null,
        $maxFetchAttempts = ImportSpecification::DEFAULT_FETCH_ATTEMPTS
    ) {
        return new ConnectionContext(
            $cacheAdvice ?: CacheAdvice::SHOULD_NOT_CACHE(),
            $fetchExceptionHandler ?: function () {
                // Intentionally empty.
            },
            $maxFetchAttempts
        );
    }
}
