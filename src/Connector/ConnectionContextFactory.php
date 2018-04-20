<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class ConnectionContextFactory
{
    use StaticClass;

    public static function create(ImportSpecification $specification): ConnectionContext
    {
        return new ConnectionContext(
            $specification->mustCache(),
            $specification->getFetchExceptionHandler(),
            $specification->getMaxFetchAttempts()
        );
    }
}
