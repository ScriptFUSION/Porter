<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Specification\Specification;
use ScriptFUSION\StaticClass;

final class ImportConnectorFactory
{
    use StaticClass;

    /**
     * @param Connector|AsyncConnector $connector
     * @param Specification $specification
     *
     * @return ImportConnector
     */
    public static function create($connector, Specification $specification): ImportConnector
    {
        return new ImportConnector(
            $connector,
            new ConnectionContext($specification->mustCache()),
            $specification->getRecoverableExceptionHandler(),
            $specification->getMaxFetchAttempts()
        );
    }
}
