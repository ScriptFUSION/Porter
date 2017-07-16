<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class ConnectionContextFactory
{
    use StaticClass;

    public static function create(ImportSpecification $specification)
    {
        return new ConnectionContext(
            $specification->getCacheAdvice(),
            $specification->getFetchExceptionHandler(),
            $specification->getMaxFetchAttempts()
        );
    }
}
