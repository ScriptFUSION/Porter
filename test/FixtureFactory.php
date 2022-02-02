<?php
declare(strict_types=1);

namespace ScriptFUSIONTest;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class FixtureFactory
{
    use StaticClass;

    public static function buildImportConnector(
        Connector $connector,
        RecoverableExceptionHandler $recoverableExceptionHandler = null,
        Provider $provider = null,
        int $maxFetchAttempts = ImportSpecification::DEFAULT_FETCH_ATTEMPTS,
        bool $mustCache = false
    ): ImportConnector {
        return new ImportConnector(
            $provider ?? \Mockery::mock(Provider::class),
            $connector,
            $recoverableExceptionHandler ?: \Mockery::spy(RecoverableExceptionHandler::class),
            $maxFetchAttempts,
            $mustCache,
            MockFactory::mockThrottle()
        );
    }
}
