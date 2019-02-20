<?php
declare(strict_types=1);

namespace ScriptFUSIONTest;

use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class FixtureFactory
{
    use StaticClass;

    /**
     * Builds ConnectionContexts with sane defaults for testing.
     */
    public static function buildConnectionContext(bool $mustCache = false): ConnectionContext
    {
        return new ConnectionContext($mustCache);
    }

    public static function buildImportConnector(
        Connector $connector,
        ConnectionContext $context = null,
        RecoverableExceptionHandler $recoverableExceptionHandler = null,
        int $maxFetchAttempts = ImportSpecification::DEFAULT_FETCH_ATTEMPTS
    ): ImportConnector {
        return new ImportConnector(
            $connector,
            $context ?: self::buildConnectionContext(),
            $recoverableExceptionHandler ?: \Mockery::spy(RecoverableExceptionHandler::class),
            $maxFetchAttempts
        );
    }
}
