<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Async\Throttle\NullThrottle;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Specification\Specification;
use ScriptFUSION\StaticClass;

/**
 * @internal For internal use only.
 */
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
        if ($specification instanceof AsyncImportSpecification) {
            $throttle = $specification->getThrottle();

            if ($throttle instanceof NullThrottle && $connector instanceof ThrottledConnector) {
                $throttle = $connector->getThrottle();
            }
        }

        return new ImportConnector(
            $connector,
            $specification->getRecoverableExceptionHandler(),
            $specification->getMaxFetchAttempts(),
            $specification->mustCache(),
            $throttle ?? null
        );
    }
}
