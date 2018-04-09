<?php
namespace ScriptFUSIONTest;

use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\StatelessFetchExceptionHandler;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class FixtureFactory
{
    use StaticClass;

    /**
     * Builds ConnectionContexts with sane defaults for testing.
     *
     * @param bool $cacheAdvice
     * @param callable|null $fetchExceptionHandler
     * @param int $maxFetchAttempts
     *
     * @return ConnectionContext
     */
    public static function buildConnectionContext(
        $cacheAdvice = false,
        callable $fetchExceptionHandler = null,
        $maxFetchAttempts = ImportSpecification::DEFAULT_FETCH_ATTEMPTS
    ) {
        return new ConnectionContext(
            $cacheAdvice,
            $fetchExceptionHandler ?: new StatelessFetchExceptionHandler(static function () {
                // Intentionally empty.
            }),
            $maxFetchAttempts
        );
    }
}
