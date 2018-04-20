<?php
declare(strict_types=1);

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
     */
    public static function buildConnectionContext(
        bool $cacheAdvice = false,
        callable $fetchExceptionHandler = null,
        int $maxFetchAttempts = ImportSpecification::DEFAULT_FETCH_ATTEMPTS
    ): ConnectionContext {
        return new ConnectionContext(
            $cacheAdvice,
            $fetchExceptionHandler ?: new StatelessFetchExceptionHandler(static function () {
                // Intentionally empty.
            }),
            $maxFetchAttempts
        );
    }
}
