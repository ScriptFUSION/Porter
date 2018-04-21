<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class ImportConnectorFactory
{
    use StaticClass;

    /**
     * @param Connector|AsyncConnector $connector
     * @param ImportSpecification $specification
     *
     * @return ImportConnector
     */
    public static function create($connector, ImportSpecification $specification): ImportConnector
    {
        return new ImportConnector(
            $connector,
            new ConnectionContext($specification->mustCache()),
            $specification->getRecoverableExceptionHandler(),
            $specification->getMaxFetchAttempts()
        );
    }
}
