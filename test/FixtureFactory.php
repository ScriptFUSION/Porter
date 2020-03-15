<?php
declare(strict_types=1);

namespace ScriptFUSIONTest;

use Amp\Success;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class FixtureFactory
{
    use StaticClass;

    public static function buildImportConnector(
        Connector $connector,
        RecoverableExceptionHandler $recoverableExceptionHandler = null,
        int $maxFetchAttempts = ImportSpecification::DEFAULT_FETCH_ATTEMPTS,
        bool $mustCache = false
    ): ImportConnector {
        return new ImportConnector(
            $connector,
            $recoverableExceptionHandler ?: \Mockery::spy(RecoverableExceptionHandler::class),
            $maxFetchAttempts,
            $mustCache,
            \Mockery::mock(Throttle::class)
                ->shouldReceive('join')
                    ->andReturn(new Success(true))
                ->getMock()
        );
    }
}
