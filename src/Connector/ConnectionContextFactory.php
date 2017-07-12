<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Specification\ImportSpecification;

final class ConnectionContextFactory
{
    public static function create(ImportSpecification $specification)
    {
        return new ConnectionContext(
            $specification->getCacheAdvice(),
            $specification->getFetchExceptionHandler(),
            $specification->getMaxFetchAttempts()
        );
    }
}
